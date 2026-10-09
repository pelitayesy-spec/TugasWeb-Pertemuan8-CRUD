<?php
declare(strict_types=1);
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = Database::getInstance()->getConnection();
[$daftarKategori, $daftarSupplier] = ambil_pilihan($pdo);
$idKategori = array_map('intval', array_column($daftarKategori, 'id'));
$idSupplier = array_map('intval', array_column($daftarSupplier, 'id'));

$produk = ['nama_produk' => '', 'kategori_id' => '', 'supplier_id' => '', 'harga' => '', 'stok' => ''];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors['umum'] = 'Sesi form tidak valid. Muat ulang halaman lalu coba lagi.';
        $hasil  = validasi_produk($_POST, $idKategori, $idSupplier);
        $produk = $hasil['old'];
    } else {
        $hasil  = validasi_produk($_POST, $idKategori, $idSupplier);
        $produk = $hasil['old'];
        $errors = $hasil['errors'];

        if (!$errors) {
            try {
                $stmt = $pdo->prepare(
                    'INSERT INTO produk (nama_produk, kategori_id, supplier_id, harga, stok)
                     VALUES (:nama, :kategori, :supplier, :harga, :stok)'
                );
                $stmt->execute([
                    ':nama'     => $hasil['clean']['nama_produk'],
                    ':kategori' => $hasil['clean']['kategori_id'],
                    ':supplier' => $hasil['clean']['supplier_id'],
                    ':harga'    => $hasil['clean']['harga'],
                    ':stok'     => $hasil['clean']['stok'],
                ]);
                set_flash('success', 'Produk "' . $hasil['clean']['nama_produk'] . '" berhasil ditambahkan.');
                redirect('index.php');   // Post/Redirect/Get
            } catch (PDOException $ex) {
                error_log('Gagal insert produk: ' . $ex->getMessage());
                $errors['umum'] = 'Produk gagal disimpan. Coba lagi beberapa saat.';
            }
        }
    }
}

$pageTitle = 'Tambah produk';
$activeNav = 'tambah';
$action    = 'create.php';
$tombol    = 'Simpan produk';
require __DIR__ . '/includes/header.php';
?>
<div class="page-head"><h1>Tambah produk</h1></div>
<?php require __DIR__ . '/includes/form_produk.php'; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
