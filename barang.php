<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'config/koneksi.php';
if (!$koneksi) {
    die("Connection failed: " . mysqli_connect_error());
}
$page='barang';

// Tambah
if(isset($_POST['tambah'])){
    echo "POST received<br>";
    $nama = strtoupper(mysqli_real_escape_string($koneksi, $_POST['nama_barang']));
    $harga = floatval($_POST['harga']);
    $stok = intval($_POST['stok']);
    if($stok < 0){ $msg = 'Stok tidak boleh negatif!'; }
    else {
        $result = mysqli_query($koneksi, "INSERT INTO barang (nama_barang,harga,stok) VALUES ('$nama',$harga,$stok)");
        if ($result) {
            $msg = 'Barang berhasil ditambahkan';
            header('Location: barang.php'); exit;
        } else {
            $msg = 'Error: ' . mysqli_error($koneksi);
        }
    }
}

// Update
if(isset($_POST['update'])){
    $id = intval($_POST['id_barang']);
    $nama = strtoupper(mysqli_real_escape_string($koneksi, $_POST['nama_barang']));
    $harga = floatval($_POST['harga']);
    $stok = intval($_POST['stok']);
    if($stok < 0){ $msg = 'Stok tidak boleh negatif!'; }
    else {
        $result = mysqli_query($koneksi, "UPDATE barang SET nama_barang='$nama', harga=$harga, stok=$stok WHERE id_barang=$id");
        if ($result) {
            $msg = 'Barang berhasil diupdate';
            header('Location: barang.php'); exit;
        } else {
            $msg = 'Error: ' . mysqli_error($koneksi);
        }
    }
}

// Hapus
if(isset($_GET['hapus'])){
    $id = intval($_GET['hapus']);
    mysqli_query($koneksi, "DELETE FROM barang WHERE id_barang=$id") or die(mysqli_error($koneksi));
    header('Location: barang.php');
    exit;
}

$data = mysqli_query($koneksi, "SELECT * FROM barang ORDER BY id_barang ASC");
?>
<!doctype html>
<html lang='en'>
<head>
<meta charset='utf-8'>
<title>Barang - Toko Donny</title>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel='stylesheet' href='assets/css/style.css'>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class='main-content'>
<div class='container'>
  <div class='breadcrumb'><a href='dashboard.php'>Dashboard</a> > Barang</div>
  <h3>Manajemen Barang</h3>
  <?php if(isset($msg)){ echo "<div style='background: #fff3cd; color: #856404; padding: 10px; border-radius: 4px; margin-bottom: 20px;'>$msg</div>"; } ?>
  <div class='mb-3'>
    <button class='btn btn-primary' data-bs-toggle='modal' data-bs-target='#modalTambah'>Tambah Barang</button>
  </div>

  <div class='card'>
    <div class='card-body'>
      <h5 class='card-title'>Daftar Barang</h5>
      <div class='mb-3'><input type='text' id='search-barang' placeholder='Cari barang...' class='form-control'></div>
      <div class='table-wrap'>
      <table class='table'>
        <thead><tr><th>ID</th><th>Nama</th><th>Harga</th><th>Stok</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php while($r = mysqli_fetch_assoc($data)){ ?>
          <tr>
            <td><?= $r['id_barang'] ?></td>
            <td><?= $r['nama_barang'] ?></td>
            <td>Rp <?= number_format($r['harga'],2,',','.') ?></td>
            <td><?= $r['stok'] ?></td>
            <td>
              <a href='barang.php?edit=<?= $r['id_barang'] ?>' class='btn btn-warning'>Edit</a>
              <a href='javascript:confirmDelete("barang.php?hapus=<?= $r['id_barang'] ?>")' class='btn btn-danger'>Hapus</a>
            </td>
          </tr>
        <?php } ?>
        </tbody>
      </table>
      </div>
    </div>
  </div>

<!-- Modal Tambah Barang -->
<div class='modal fade' id='modalTambah' tabindex='-1'>
  <div class='modal-dialog'>
    <div class='modal-content'>
      <div class='modal-header'>
        <h5 class='modal-title'>Tambah Barang</h5>
        <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
      </div>
      <div class='modal-body'>
        <form method='post' onsubmit="document.querySelector('button[name=tambah]').innerHTML='Loading...';">
          <div class='mb-3'>
            <label class='form-label'>Nama Barang</label>
            <input class='form-control' name='nama_barang' placeholder='Nama Barang' required>
          </div>
          <div class='mb-3'>
            <label class='form-label'>Harga</label>
            <input type='number' step='0.01' class='form-control' name='harga' placeholder='Harga' required>
          </div>
          <div class='mb-3'>
            <label class='form-label'>Stok</label>
            <input type='number' class='form-control' name='stok' placeholder='Stok' min='0' required>
          </div>
          <button type='submit' class='btn btn-primary' name='tambah'>Tambah</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php if(isset($_GET['edit'])){
    $id = intval($_GET['edit']);
    $row = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM barang WHERE id_barang=$id"));
?>
<div class='card mt-3'>
  <div class='card-body'>
    <h5 class='card-title'>Edit Barang</h5>
    <form method='post' style='display: flex; gap: 10px; align-items: end; margin-bottom: 10px;'>
      <input type='hidden' name='id_barang' value='<?= $row['id_barang'] ?>'>
      <div style='flex: 2;'><input class='form-control' name='nama_barang' value='<?= $row['nama_barang'] ?>' required></div>
      <div style='flex: 1;'><input type='number' step='0.01' class='form-control' name='harga' value='<?= $row['harga'] ?>' required></div>
      <div style='flex: 1;'><input type='number' class='form-control' name='stok' value='<?= $row['stok'] ?>' min='0' required></div>
      <div style='flex: 1;'><button class='btn btn-success' name='update'>Update</button></div>
    </form>
  </div>
</div>
<?php } ?>

<!-- Modal Hapus -->
<div class='modal fade' id='modalHapus' tabindex='-1'>
  <div class='modal-dialog'>
    <div class='modal-content'>
      <div class='modal-header'>
        <h5 class='modal-title'>Konfirmasi Hapus</h5>
        <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
      </div>
      <div class='modal-body'>
        Yakin ingin menghapus barang ini?
      </div>
      <div class='modal-footer'>
        <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Batal</button>
        <a id='linkHapus' href='#' class='btn btn-danger'>Hapus</a>
      </div>
    </div>
  </div>
</div>

</div>
</div>
<footer class='footer'>Dibuat untuk UTS - Toko Donny</footer>
<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js'></script>
<script src='assets/js/script.js'></script>
</body>
</html>
