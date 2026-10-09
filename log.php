<?php
declare(strict_types=1);
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pdo  = Database::getInstance()->getConnection();
$stmt = $pdo->prepare('SELECT aksi, deskripsi, created_at FROM log_aktivitas ORDER BY id DESC LIMIT :limit');
$stmt->bindValue(':limit', 50, PDO::PARAM_INT);
$stmt->execute();
$logs = $stmt->fetchAll();

$pageTitle = 'Log aktivitas';
$activeNav = 'log';
require __DIR__ . '/includes/header.php';
?>
<div class="page-head">
    <div>
        <h1>Log aktivitas</h1>
        <p class="muted">50 catatan terakhir. Dicatat otomatis setiap ada produk yang dihapus.</p>
    </div>
</div>

<div class="card table-wrap">
    <table>
        <thead>
            <tr><th>Waktu</th><th>Aksi</th><th>Keterangan</th></tr>
        </thead>
        <tbody>
        <?php if (!$logs): ?>
            <tr><td colspan="3" class="empty">Belum ada aktivitas tercatat.</td></tr>
        <?php endif; ?>
        <?php foreach ($logs as $log): ?>
            <tr>
                <td><?= e($log['created_at']) ?></td>
                <td><span class="tag tag--danger"><?= e($log['aksi']) ?></span></td>
                <td><?= e($log['deskripsi']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
