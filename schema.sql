-- ============================================================
-- Tugas Rutin 8 - CRUD Inventaris
-- Cara import: mysql -u root -p < schema.sql
-- atau lewat phpMyAdmin > tab Import > pilih file ini
-- ============================================================

CREATE DATABASE IF NOT EXISTS inventaris_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE inventaris_db;

-- Hapus tabel lama (urutan: anak dulu, baru induk)
DROP TABLE IF EXISTS log_aktivitas;
DROP TABLE IF EXISTS produk;
DROP TABLE IF EXISTS kategori;
DROP TABLE IF EXISTS supplier;

-- ---------- Tabel 1: kategori ----------
CREATE TABLE kategori (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama_kategori VARCHAR(100) NOT NULL,
  deskripsi     VARCHAR(255) NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_kategori_nama (nama_kategori)
) ENGINE=InnoDB;

-- ---------- Tabel 2: supplier ----------
CREATE TABLE supplier (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama_supplier VARCHAR(150) NOT NULL,
  kontak        VARCHAR(50)  NULL,
  alamat        VARCHAR(255) NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

-- ---------- Tabel 3: produk (punya 2 foreign key) ----------
CREATE TABLE produk (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama_produk VARCHAR(150) NOT NULL,
  kategori_id INT UNSIGNED NOT NULL,
  supplier_id INT UNSIGNED NOT NULL,
  harga       DECIMAL(12,2) NOT NULL DEFAULT 0,
  stok        INT UNSIGNED NOT NULL DEFAULT 0,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_produk_kategori (kategori_id),
  KEY idx_produk_supplier (supplier_id),
  CONSTRAINT fk_produk_kategori FOREIGN KEY (kategori_id)
    REFERENCES kategori (id) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_produk_supplier FOREIGN KEY (supplier_id)
    REFERENCES supplier (id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------- Tabel tambahan (BONUS): log aktivitas ----------
-- Tidak memakai FK supaya riwayat tetap ada walaupun produknya sudah dihapus.
CREATE TABLE log_aktivitas (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  aksi       VARCHAR(20)  NOT NULL,
  deskripsi  VARCHAR(500) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

-- ---------- Data seed ----------
INSERT INTO kategori (nama_kategori, deskripsi) VALUES
  ('Elektronik',     'Perangkat elektronik dan aksesorisnya'),
  ('Alat Tulis',     'Perlengkapan menulis dan kantor'),
  ('Perabot',        'Meja, kursi, dan lemari'),
  ('Makanan Ringan', 'Snack dan minuman kemasan'),
  ('Kebersihan',     'Perlengkapan kebersihan ruangan');

INSERT INTO supplier (nama_supplier, kontak, alamat) VALUES
  ('PT Maju Jaya Elektronik', '061-4510001',  'Jl. Gatot Subroto No. 12, Medan'),
  ('CV Tulis Sejahtera',      '061-4510002',  'Jl. Sisingamangaraja No. 45, Medan'),
  ('UD Kayu Mandiri',         '0812-3456-7801', 'Jl. Setia Budi No. 8, Medan'),
  ('PT Snack Nusantara',      '0813-9876-5402', 'Jl. Sunggal No. 21, Medan'),
  ('CV Bersih Selalu',        '0821-1122-3303', 'Jl. Gagak Hitam No. 5, Medan');

INSERT INTO produk (nama_produk, kategori_id, supplier_id, harga, stok) VALUES
  ('Mouse Wireless Logitech',   1, 1,   185000, 40),
  ('Keyboard Mekanik 87 Key',   1, 1,   450000, 18),
  ('Flashdisk 64 GB',           1, 1,    75000, 60),
  ('Pulpen Gel Hitam (Lusin)',  2, 2,    36000, 120),
  ('Buku Tulis A5 Isi 80',      2, 2,    12000, 200),
  ('Meja Belajar Minimalis',    3, 3,   650000, 9),
  ('Kursi Kantor Ergonomis',    3, 3,   875000, 7),
  ('Keripik Singkong Pedas',    4, 4,    15000, 150),
  ('Air Mineral 600 ml (Dus)',  4, 4,    48000, 80),
  ('Cairan Pembersih Lantai 1 L', 5, 5,  22000, 55);
