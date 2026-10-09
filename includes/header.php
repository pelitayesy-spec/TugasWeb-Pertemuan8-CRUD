<?php
/** Variabel yang diharapkan: $pageTitle (string), $activeNav (string) */
$flash = get_flash();
$activeNav = $activeNav ?? '';
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? 'Inventaris') ?> | Inventaris Gudang</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
    <div class="wrap topbar__inner">
        <a class="brand" href="index.php">Inventaris Gudang</a>
        <nav class="nav" aria-label="Menu utama">
            <a href="index.php" class="<?= $activeNav === 'produk' ? 'is-active' : '' ?>">Produk</a>
            <a href="create.php" class="<?= $activeNav === 'tambah' ? 'is-active' : '' ?>">Tambah produk</a>
            <a href="log.php" class="<?= $activeNav === 'log' ? 'is-active' : '' ?>">Log aktivitas</a>
        </nav>
    </div>
</header>
<main class="wrap">
<?php if ($flash): ?>
    <div class="flash flash--<?= e($flash['type']) ?>" role="status">
        <?= e($flash['message']) ?>
    </div>
<?php endif; ?>
