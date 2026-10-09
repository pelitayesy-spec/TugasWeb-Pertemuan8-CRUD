<?php
declare(strict_types=1);
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = Database::getInstance()->getConnection();

// ---------- Pencarian & pagination ----------
$perHalaman = 5;
$q          = trim((string) ($_GET['q'] ?? ''));
$halaman    = max(1, (int) ($_GET['page'] ?? 1));

$where  = '';
$params = [];
if ($q !== '') {
    // escape karakter wildcard LIKE supaya dicari sebagai teks biasa
    $like   = '%' . addcslashes($q, '%_\\') . '%';
    $where  = 'WHERE p.nama_produk LIKE :q1 OR k.nama_kategori LIKE :q2 OR s.nama_supplier LIKE :q3';
    $params = [':q1' => $like, ':q2' => $like, ':q3' => $like];
}

// JOIN produk dengan kategori dan supplier
$from = 'FROM produk p
         INNER JOIN kategori k ON k.id = p.kategori_id
         INNER JOIN supplier s ON s.id = p.supplier_id';

// Hitung total data untuk pagination
$stmt = $pdo->prepare("SELECT COUNT(*) $from $where");
$stmt->execute($params);
$totalData    = (int) $stmt->fetchColumn();
$totalHalaman = max(1, (int) ceil($totalData / $perHalaman));
$halaman      = min($halaman, $totalHalaman);
$offset       = ($halaman - 1) * $perHalaman;

// Ambil data halaman ini
$stmt = $pdo->prepare(
    "SELECT p.id, p.nama_produk, p.harga, p.stok, k.nama_kategori, s.nama_supplier
     $from $where
     ORDER BY p.id DESC
     LIMIT :limit OFFSET :offset"
);
foreach ($params as $nama => $nilai) {
    $stmt->bindValue($nama, $nilai, PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $perHalaman, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$produk = $stmt->fetchAll();

/** Bangun URL halaman dengan mempertahankan kata kunci pencarian */
function url_halaman(int $page, string $q): string
{
    $query = ['page' => $page];
    if ($q !== '') {
        $query['q'] = $q;
    }
    return 'index.php?' . http_build_query($query);
}

$pageTitle = 'Daftar produk';
$activeNav = 'produk';
require __DIR__ . '/includes/header.php';
?>

<div class="page-head">
    <div>
        <h1>Daftar produk</h1>
        <p class="muted"><?= $totalData ?> produk<?= $q !== '' ? ' cocok dengan "' . e($q) . '"' : ' tercatat' ?></p>
    </div>
    <div class="actions">
        <a class="btn" href="export.php<?= $q !== '' ? '?' . e(http_build_query(['q' => $q])) : '' ?>">Export CSV</a>
        <a class="btn btn--primary" href="create.php">Tambah produk</a>
    </div>
</div>

<form method="get" action="index.php" class="search" role="search">
    <label for="q" class="sr-only">Cari produk</label>
    <input type="search" id="q" name="q" value="<?= e($q) ?>" placeholder="Cari nama produk, kategori, atau supplier">
    <button type="submit" class="btn">Cari</button>
    <?php if ($q !== ''): ?><a href="index.php" class="btn btn--ghost">Reset</a><?php endif; ?>
</form>

<div class="card table-wrap">
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama produk</th>
                <th>Kategori</th>
                <th>Supplier</th>
                <th class="num">Harga</th>
                <th class="num">Stok</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php if (!$produk): ?>
            <tr>
                <td colspan="7" class="empty">
                    <?= $q !== '' ? 'Tidak ada produk yang cocok. Coba kata kunci lain.' : 'Belum ada produk. Tambahkan produk pertama.' ?>
                </td>
            </tr>
        <?php endif; ?>
        <?php foreach ($produk as $i => $row): ?>
            <tr>
                <td><?= $offset + $i + 1 ?></td>
                <td><?= e($row['nama_produk']) ?></td>
                <td><span class="tag"><?= e($row['nama_kategori']) ?></span></td>
                <td><?= e($row['nama_supplier']) ?></td>
                <td class="num"><?= e(rupiah($row['harga'])) ?></td>
                <td class="num <?= (int) $row['stok'] <= 10 ? 'stok-rendah' : '' ?>"><?= (int) $row['stok'] ?></td>
                <td class="row-actions">
                    <a href="edit.php?id=<?= (int) $row['id'] ?>">Edit</a>
                    <a href="delete.php?id=<?= (int) $row['id'] ?>" class="danger">Hapus</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if ($totalHalaman > 1): ?>
<nav class="pagination" aria-label="Halaman">
    <?php if ($halaman > 1): ?>
        <a href="<?= e(url_halaman($halaman - 1, $q)) ?>">Sebelumnya</a>
    <?php endif; ?>
    <?php for ($p = 1; $p <= $totalHalaman; $p++): ?>
        <a href="<?= e(url_halaman($p, $q)) ?>" class="<?= $p === $halaman ? 'is-active' : '' ?>"
           <?= $p === $halaman ? 'aria-current="page"' : '' ?>><?= $p ?></a>
    <?php endfor; ?>
    <?php if ($halaman < $totalHalaman): ?>
        <a href="<?= e(url_halaman($halaman + 1, $q)) ?>">Berikutnya</a>
    <?php endif; ?>
</nav>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
