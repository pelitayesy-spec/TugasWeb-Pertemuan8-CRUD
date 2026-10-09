<?php
declare(strict_types=1);
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = Database::getInstance()->getConnection();

$id = ambil_id($_GET['id'] ?? $_POST['id'] ?? null);
if ($id === null) {
    set_flash('error', 'ID produk tidak valid.');
    redirect('index.php');
}

$sqlProduk = 'SELECT p.id, p.nama_produk, p.harga, p.stok, k.nama_kategori, s.nama_supplier
              FROM produk p
              INNER JOIN kategori k ON k.id = p.kategori_id
              INNER JOIN supplier s ON s.id = p.supplier_id
              WHERE p.id = :id';

// ---------- Eksekusi hapus (hanya lewat POST) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        set_flash('error', 'Sesi form tidak valid. Penghapusan dibatalkan.');
        redirect('index.php');
    }

    try {
        // BONUS: transaction. Log dan delete harus berhasil bersamaan, atau batal semua.
        $pdo->beginTransaction();

        $stmt = $pdo->prepare($sqlProduk . ' FOR UPDATE');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            $pdo->rollBack();
            set_flash('error', 'Produk tidak ditemukan. Mungkin sudah dihapus.');
            redirect('index.php');
        }

        $deskripsi = sprintf(
            'Produk #%d "%s" (kategori: %s, supplier: %s, stok: %d) dihapus.',
            $row['id'], $row['nama_produk'], $row['nama_kategori'], $row['nama_supplier'], $row['stok']
        );

        $stmt = $pdo->prepare('INSERT INTO log_aktivitas (aksi, deskripsi) VALUES (:aksi, :deskripsi)');
        $stmt->execute([':aksi' => 'DELETE', ':deskripsi' => $deskripsi]);

        $stmt = $pdo->prepare('DELETE FROM produk WHERE id = :id');
        $stmt->execute([':id' => $id]);

        $pdo->commit();
        set_flash('success', 'Produk "' . $row['nama_produk'] . '" berhasil dihapus.');
    } catch (PDOException $ex) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Gagal hapus produk: ' . $ex->getMessage());
        set_flash('error', 'Produk gagal dihapus. Tidak ada data yang berubah.');
    }
    redirect('index.php');
}

// ---------- Halaman konfirmasi (GET) ----------
$stmt = $pdo->prepare($sqlProduk);
$stmt->execute([':id' => $id]);
$row = $stmt->fetch();
if (!$row) {
    set_flash('error', 'Produk tidak ditemukan. Mungkin sudah dihapus.');
    redirect('index.php');
}

$pageTitle = 'Hapus produk';
$activeNav = 'produk';
require __DIR__ . '/includes/header.php';
?>
<div class="page-head"><h1>Hapus produk</h1></div>

<div class="card confirm">
    <p>Produk berikut akan dihapus permanen. Tindakan ini tidak bisa dibatalkan.</p>
    <dl class="detail">
        <dt>Nama produk</dt><dd><?= e($row['nama_produk']) ?></dd>
        <dt>Kategori</dt><dd><?= e($row['nama_kategori']) ?></dd>
        <dt>Supplier</dt><dd><?= e($row['nama_supplier']) ?></dd>
        <dt>Harga</dt><dd><?= e(rupiah($row['harga'])) ?></dd>
        <dt>Stok</dt><dd><?= (int) $row['stok'] ?></dd>
    </dl>
    <form method="post" action="delete.php" class="actions">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
        <button type="submit" class="btn btn--danger">Ya, hapus produk</button>
        <a href="index.php" class="btn">Batal</a>
    </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
