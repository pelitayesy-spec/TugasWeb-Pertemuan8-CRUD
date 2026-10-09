<?php
declare(strict_types=1);
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = Database::getInstance()->getConnection();

$q      = trim((string) ($_GET['q'] ?? ''));
$where  = '';
$params = [];
if ($q !== '') {
    $like   = '%' . addcslashes($q, '%_\\') . '%';
    $where  = 'WHERE p.nama_produk LIKE :q1 OR k.nama_kategori LIKE :q2 OR s.nama_supplier LIKE :q3';
    $params = [':q1' => $like, ':q2' => $like, ':q3' => $like];
}

$stmt = $pdo->prepare(
    "SELECT p.id, p.nama_produk, k.nama_kategori, s.nama_supplier, p.harga, p.stok
     FROM produk p
     INNER JOIN kategori k ON k.id = p.kategori_id
     INNER JOIN supplier s ON s.id = p.supplier_id
     $where
     ORDER BY p.id"
);
$stmt->execute($params);

/** Cegah CSV injection: sel yang diawali = + - @ diberi tanda kutip */
function aman_csv($nilai)
{
    if (is_string($nilai) && $nilai !== '' && strpos('=+-@', $nilai[0]) !== false) {
        return "'" . $nilai;
    }
    return $nilai;
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="laporan-inventaris-' . date('Ymd-His') . '.csv"');

$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF");   // BOM agar Excel membaca UTF-8 dengan benar
fputcsv($out, ['ID', 'Nama Produk', 'Kategori', 'Supplier', 'Harga', 'Stok']);
while ($row = $stmt->fetch()) {
    fputcsv($out, [
        $row['id'],
        aman_csv($row['nama_produk']),
        aman_csv($row['nama_kategori']),
        aman_csv($row['nama_supplier']),
        $row['harga'],
        $row['stok'],
    ]);
}
fclose($out);
exit;
