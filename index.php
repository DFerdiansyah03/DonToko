<?php
include 'config/koneksi.php';
header('Location: dashboard.php');
exit;
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Toko Donny</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/css/style.css">
  </head>
  <body>
  <?php $page='home'; include 'navbar.php'; ?>
  <div class="container">
    <div class="py-4 text-center">
      <h2>Selamat datang di Toko Donny</h2>
      <p class="lead">Aplikasi penjualan sederhana untuk tugas UTS — PHP + MySQL + Bootstrap</p>
      <a href="dashboard.php" class="btn btn-primary">Lihat Dashboard</a>
    </div>
    <div class="row">
      <div class="col-md-4">
        <div class="card mb-3">
          <div class="card-body">
            <h5 class="card-title">Barang</h5>
            <p class="card-text">Kelola daftar barang (tambah/edit/hapus)</p>
            <a href="barang.php" class="btn btn-outline-primary">Kelola Barang</a>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card mb-3">
          <div class="card-body">
            <h5 class="card-title">Pembeli</h5>
            <p class="card-text">Kelola data pelanggan</p>
            <a href="pembeli.php" class="btn btn-outline-primary">Kelola Pembeli</a>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card mb-3">
          <div class="card-body">
            <h5 class="card-title">Transaksi</h5>
            <p class="card-text">Catat transaksi penjualan</p>
            <a href="transaksi.php" class="btn btn-outline-primary">Kelola Transaksi</a>
          </div>
        </div>
      </div>
    </div>
    <p class="footer">Dibuat untuk UTS - Toko Donny</p>
  </div>
  <script src="assets/js/script.js"></script>
  </body>
</html>
