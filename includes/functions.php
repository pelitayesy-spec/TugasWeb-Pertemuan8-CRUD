<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Tampilkan pesan ramah jika ada exception yang tidak tertangkap
set_exception_handler(function (Throwable $e): void {
    error_log((string) $e);
    http_response_code(500);
    $pesan = $e instanceof RuntimeException
        ? $e->getMessage()
        : 'Terjadi kesalahan pada server. Silakan coba lagi.';
    echo '<!doctype html><meta charset="utf-8"><title>Error</title>'
       . '<body style="font-family:system-ui;max-width:560px;margin:4rem auto;padding:0 1rem">'
       . '<h1>Ups, ada masalah</h1><p>' . htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8') . '</p></body>';
});

/** Escape output HTML (pencegah XSS) */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/** Flash message: disimpan di session, tampil sekali, lalu hilang */
function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

/** CSRF token untuk semua form POST */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_verify(): bool
{
    $token = $_POST['csrf_token'] ?? '';
    return is_string($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function rupiah($angka): string
{
    return 'Rp ' . number_format((float) $angka, 0, ',', '.');
}

/** Ambil id dari query string / form, atau null kalau tidak valid */
function ambil_id($nilai): ?int
{
    $id = filter_var($nilai, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $id === false ? null : $id;
}

/**
 * Validasi input form produk (dipakai create & edit).
 * Mengembalikan: old (nilai mentah untuk isi ulang form), clean (nilai bersih), errors.
 */
function validasi_produk(array $input, array $idKategori, array $idSupplier): array
{
    $old = [
        'nama_produk' => trim((string) ($input['nama_produk'] ?? '')),
        'kategori_id' => (string) ($input['kategori_id'] ?? ''),
        'supplier_id' => (string) ($input['supplier_id'] ?? ''),
        'harga'       => trim((string) ($input['harga'] ?? '')),
        'stok'        => trim((string) ($input['stok'] ?? '')),
    ];
    $errors = [];

    $panjang = mb_strlen($old['nama_produk']);
    if ($panjang < 3 || $panjang > 150) {
        $errors['nama_produk'] = 'Nama produk wajib diisi, 3 sampai 150 karakter.';
    }

    $kategori = filter_var($old['kategori_id'], FILTER_VALIDATE_INT);
    if ($kategori === false || !in_array($kategori, $idKategori, true)) {
        $errors['kategori_id'] = 'Pilih salah satu kategori.';
    }

    $supplier = filter_var($old['supplier_id'], FILTER_VALIDATE_INT);
    if ($supplier === false || !in_array($supplier, $idSupplier, true)) {
        $errors['supplier_id'] = 'Pilih salah satu supplier.';
    }

    $harga = filter_var($old['harga'], FILTER_VALIDATE_FLOAT);
    if ($harga === false || $harga < 0 || $harga > 9999999999) {
        $errors['harga'] = 'Harga harus berupa angka 0 atau lebih.';
    }

    $stok = filter_var($old['stok'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 1000000]]);
    if ($stok === false) {
        $errors['stok'] = 'Stok harus berupa bilangan bulat 0 atau lebih.';
    }

    return [
        'old'    => $old,
        'clean'  => [
            'nama_produk' => $old['nama_produk'],
            'kategori_id' => $kategori,
            'supplier_id' => $supplier,
            'harga'       => $harga === false ? 0 : round($harga, 2),
            'stok'        => $stok,
        ],
        'errors' => $errors,
    ];
}

/** Ambil daftar kategori & supplier untuk dropdown (prepared statement) */
function ambil_pilihan(PDO $pdo): array
{
    $stmt = $pdo->prepare('SELECT id, nama_kategori FROM kategori ORDER BY nama_kategori');
    $stmt->execute();
    $kategori = $stmt->fetchAll();

    $stmt = $pdo->prepare('SELECT id, nama_supplier FROM supplier ORDER BY nama_supplier');
    $stmt->execute();
    $supplier = $stmt->fetchAll();

    return [$kategori, $supplier];
}
