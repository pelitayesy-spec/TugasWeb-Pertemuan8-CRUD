<?php
/**
 * Form bersama untuk create & edit.
 * Variabel: $action, $tombol, $produk (array nilai form), $errors,
 *           $daftarKategori, $daftarSupplier
 */
?>
<form method="post" action="<?= e($action) ?>" class="card form" novalidate>
    <?= csrf_field() ?>

    <?php if (!empty($errors['umum'])): ?>
        <div class="flash flash--error"><?= e($errors['umum']) ?></div>
    <?php endif; ?>

    <div class="field">
        <label for="nama_produk">Nama produk</label>
        <input type="text" id="nama_produk" name="nama_produk" maxlength="150"
               value="<?= e($produk['nama_produk']) ?>" required>
        <?php if (isset($errors['nama_produk'])): ?><p class="field__error"><?= e($errors['nama_produk']) ?></p><?php endif; ?>
    </div>

    <div class="grid-2">
        <div class="field">
            <label for="kategori_id">Kategori</label>
            <select id="kategori_id" name="kategori_id" required>
                <option value="">Pilih kategori</option>
                <?php foreach ($daftarKategori as $k): ?>
                    <option value="<?= (int) $k['id'] ?>"
                        <?= (string) $produk['kategori_id'] === (string) $k['id'] ? 'selected' : '' ?>>
                        <?= e($k['nama_kategori']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['kategori_id'])): ?><p class="field__error"><?= e($errors['kategori_id']) ?></p><?php endif; ?>
        </div>

        <div class="field">
            <label for="supplier_id">Supplier</label>
            <select id="supplier_id" name="supplier_id" required>
                <option value="">Pilih supplier</option>
                <?php foreach ($daftarSupplier as $s): ?>
                    <option value="<?= (int) $s['id'] ?>"
                        <?= (string) $produk['supplier_id'] === (string) $s['id'] ? 'selected' : '' ?>>
                        <?= e($s['nama_supplier']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['supplier_id'])): ?><p class="field__error"><?= e($errors['supplier_id']) ?></p><?php endif; ?>
        </div>
    </div>

    <div class="grid-2">
        <div class="field">
            <label for="harga">Harga (Rp)</label>
            <input type="number" id="harga" name="harga" min="0" step="0.01"
                   value="<?= e($produk['harga']) ?>" required>
            <?php if (isset($errors['harga'])): ?><p class="field__error"><?= e($errors['harga']) ?></p><?php endif; ?>
        </div>

        <div class="field">
            <label for="stok">Stok</label>
            <input type="number" id="stok" name="stok" min="0" step="1"
                   value="<?= e($produk['stok']) ?>" required>
            <?php if (isset($errors['stok'])): ?><p class="field__error"><?= e($errors['stok']) ?></p><?php endif; ?>
        </div>
    </div>

    <div class="actions">
        <button type="submit" class="btn btn--primary"><?= e($tombol) ?></button>
        <a href="index.php" class="btn">Batal</a>
    </div>
</form>
