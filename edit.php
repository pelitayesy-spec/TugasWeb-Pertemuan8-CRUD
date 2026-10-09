<?php
declare(strict_types=1);
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = Database::getInstance()->getConnection();

$id = ambil_id($_GET['id'] ?? null);
if ($id === null) {
    set_flash('error', 'ID produk tidak valid.');
    redirect('index.php');
}

// Ambil data lama
$stmt = $pdo->prepare('SELECT id, nama_produk, kategori_id, supplier_id, harga, stok FROM produk WHERE id = :id');
$stmt->execute([':id' => $id]);
$row = $stmt->fetch();
if (!$row) {
    set_flash('error', 'Produk tidak ditemukan. Mungkin sudah dihapus.');
    redirect('index.php');
}

[$daftarKategori, $daftarSupplier] = ambil_pilihan($pdo);
$idKategori = array_map('intval', array_column($daftarKategori, 'id'));
$idSupplier = array_map('intval', array_column($daftarSupplier, 'id'));

// Form pre-filled dengan data dari database
$produk = [
    'nama_produk' => (string) $row['nama_produk'],
    'kategori_id' => (string) $row['kategori_id'],
    'supplier_id' => (string) $row['supplier_id'],
    'harga'       => (string) (float) $row['harga'],
    'stok'        => (string) $row['stok'],
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hasil  = validasi_produk($_POST, $idKategori, $idSupplier);
    $produk = $hasil['old'];

    if (!csrf_verify()) {
        $errors['umum'] = 'Sesi form tidak valid. Muat ulang halaman lalu coba lagi.';
    } else {
        $errors = $hasil['errors'];
        if (!$errors) {
            try {
                $stmt = $pdo->prepare(
                    'UPDATE produk
                     SET nama_produk = :nama, kategori_id = :kategori, supplier_id = :supplier,
                         harga = :harga, stok = :stok
                     WHERE id = :id'
                );
                $stmt->execute([
                    ':nama'     => $hasil['clean']['nama_produk'],
                    ':kategori' => $hasil['clean']['kategori_id'],
                    ':supplier' => $hasil['clean']['supplier_id'],
                    ':harga'    => $hasil['clean']['harga'],
                    ':stok'     => $hasil['clean']['stok'],
                    ':id'       => $id,
                ]);
                set_flash('success', 'Produk "' . $hasil['clean']['nama_produk'] . '" berhasil diperbarui.');
                redirect('index.php');
            } catch (PDOException $ex) {
                error_log('Gagal update produk: ' . $ex->getMessage());
                $errors['umum'] = 'Perubahan gagal disimpan. Coba lagi beberapa saat.';
            }
        }
    }
}

$pageTitle = 'Edit produk';
$activeNav = 'produk';
$action    = 'edit.php?id=' . $id;
$tombol    = 'Simpan perubahan';
require __DIR__ . '/includes/header.php';
?>
<div class="page-head"><h1>Edit produk</h1></div>
<?php require __DIR__ . '/includes/form_produk.php'; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
