-- ====================================================
--  DATABASE : toko_donny
--  PROJECT  : Aplikasi Penjualan Sederhana
--  AUTHOR   : Donny
-- ====================================================

-- 1️⃣ Buat Database
CREATE DATABASE IF NOT EXISTS toko_donny;
USE toko_donny;

-- ====================================================
-- 2️⃣ Tabel Barang
-- ====================================================
CREATE TABLE IF NOT EXISTS barang (
  id_barang INT AUTO_INCREMENT PRIMARY KEY,
  nama_barang VARCHAR(100) NOT NULL,
  harga DECIMAL(10,2) NOT NULL,
  stok INT NOT NULL CHECK (stok >= 0)
);

-- ====================================================
-- 3️⃣ Tabel Pembeli
-- ====================================================
CREATE TABLE IF NOT EXISTS pembeli (
  id_pembeli INT AUTO_INCREMENT PRIMARY KEY,
  nama_pembeli VARCHAR(100) NOT NULL,
  alamat TEXT
);

-- ====================================================
-- 4️⃣ Tabel Transaksi
-- ====================================================
-- Jika MySQL versi 8+ mendukung kolom terhitung otomatis:
CREATE TABLE IF NOT EXISTS transaksi (
  id_transaksi INT AUTO_INCREMENT PRIMARY KEY,
  id_pembeli INT NOT NULL,
  id_barang INT NOT NULL,
  jumlah INT NOT NULL CHECK (jumlah > 0),
  total_harga DECIMAL(10,2) DEFAULT 0.00,
  tanggal DATE DEFAULT (CURRENT_DATE),
  FOREIGN KEY (id_pembeli) REFERENCES pembeli(id_pembeli),
  FOREIGN KEY (id_barang) REFERENCES barang(id_barang)
);



-- ====================================================
-- 7️⃣ Data Awal (Opsional)
-- ====================================================
INSERT INTO barang (nama_barang, harga, stok) VALUES
('INDOMIE GORENG', 3500, 50),
('TEH BOTOL SOSRO', 4000, 30),
('FLORIDINA', 5000, 25);

INSERT INTO pembeli (nama_pembeli, alamat) VALUES
('BUDI SANTOSO', 'Jl. Kenanga No. 12'),
('SITI AMINAH', 'Jl. Melati No. 5');

INSERT INTO transaksi (id_pembeli, id_barang, jumlah, tanggal)
VALUES
(1, 1, 2, '2025-10-11'),
(2, 3, 1, '2025-10-10');

-- ====================================================
-- 8️⃣ View untuk Laporan Penjualan
-- ====================================================
CREATE OR REPLACE VIEW v_laporan_penjualan AS
SELECT
  t.id_transaksi,
  p.nama_pembeli,
  b.nama_barang,
  t.jumlah,
  t.total_harga,
  t.tanggal
FROM transaksi t
JOIN pembeli p ON t.id_pembeli = p.id_pembeli
JOIN barang b ON t.id_barang = b.id_barang;

-- ====================================================
-- 9️⃣ View untuk Dashboard Agregat
-- ====================================================
CREATE OR REPLACE VIEW v_dashboard AS
SELECT
  COUNT(t.id_transaksi) AS total_transaksi,
  SUM(t.total_harga) AS total_pendapatan,
  (SELECT b.nama_barang
     FROM transaksi t2
     JOIN barang b ON t2.id_barang = b.id_barang
     GROUP BY b.id_barang
     ORDER BY SUM(t2.jumlah) DESC
     LIMIT 1) AS barang_terlaris
FROM transaksi t;
-- ====================================================
-- END OF SQL SCRIPT
-- ====================================================
