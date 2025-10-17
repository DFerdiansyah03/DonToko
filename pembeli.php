<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'config/koneksi.php';
if (!$koneksi) {
    die("Connection failed: " . mysqli_connect_error());
}
$page='pembeli';

if(isset($_POST['tambah'])){
    echo "POST received<br>";
    $nama = strtoupper(mysqli_real_escape_string($koneksi, $_POST['nama_pembeli']));
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $result = mysqli_query($koneksi, "INSERT INTO pembeli (nama_pembeli,alamat) VALUES ('$nama','$alamat')");
    if ($result) {
        $msg = 'Pembeli berhasil ditambahkan';
        header('Location: pembeli.php'); exit;
    } else {
        $msg = 'Error: ' . mysqli_error($koneksi);
    }
}

if(isset($_POST['update'])){
    $id = intval($_POST['id_pembeli']);
    $nama = strtoupper(mysqli_real_escape_string($koneksi, $_POST['nama_pembeli']));
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $result = mysqli_query($koneksi, "UPDATE pembeli SET nama_pembeli='$nama', alamat='$alamat' WHERE id_pembeli=$id");
    if ($result) {
        $msg = 'Pembeli berhasil diupdate';
        header('Location: pembeli.php'); exit;
    } else {
        $msg = 'Error: ' . mysqli_error($koneksi);
    }
}

if(isset($_GET['hapus'])){
    $id = intval($_GET['hapus']);
    $result = mysqli_query($koneksi, "DELETE FROM pembeli WHERE id_pembeli=$id");
    if ($result) {
        $msg = 'Pembeli berhasil dihapus';
        header('Location: pembeli.php'); exit;
    } else {
        $msg = 'Error: ' . mysqli_error($koneksi);
    }
}

$data = mysqli_query($koneksi, "SELECT * FROM pembeli ORDER BY id_pembeli ASC");
?>
<!doctype html>
<html lang='en'>
<head>
<meta charset='utf-8'>
<title>Pembeli - Toko Donny</title>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel='stylesheet' href='assets/css/style.css'>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class='main-content'>
<div class='container'>
  <div class='breadcrumb'><a href='dashboard.php'>Dashboard</a> > Pembeli</div>
  <h3>Manajemen Pembeli</h3>
  <?php if(isset($msg)){ echo "<div style='background: #fff3cd; color: #856404; padding: 10px; border-radius: 4px; margin-bottom: 20px;'>$msg</div>"; } ?>

  <div class='mb-3'>
    <button class='btn btn-primary' data-bs-toggle='modal' data-bs-target='#modalTambah'>Tambah Pembeli</button>
  </div>

  <div class='card'>
    <div class='card-body'>
      <h5 class='card-title'>Daftar Pembeli</h5>
      <div class='mb-3'><input type='text' id='search-pembeli' placeholder='Cari pembeli...' class='form-control'></div>
      <div class='table-wrap'>
      <table class='table'>
        <thead><tr><th>ID</th><th>Nama</th><th>Alamat</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php while($r = mysqli_fetch_assoc($data)){ ?>
          <tr>
            <td><?= $r['id_pembeli'] ?></td>
            <td><?= $r['nama_pembeli'] ?></td>
            <td><?= $r['alamat'] ?></td>
            <td>
              <button class='btn btn-warning' onclick='editPembeli(<?= $r['id_pembeli'] ?>, "<?= addslashes($r['nama_pembeli']) ?>", "<?= addslashes($r['alamat']) ?>")'>Edit</button>
              <a href='javascript:confirmDelete("pembeli.php?hapus=<?= $r['id_pembeli'] ?>")' class='btn btn-danger'>Hapus</a>
            </td>
          </tr>
        <?php } ?>
        </tbody>
      </table>
      </div>
    </div>
  </div>

<!-- Modal Tambah Pembeli -->
<div class='modal fade' id='modalTambah' tabindex='-1'>
  <div class='modal-dialog'>
    <div class='modal-content'>
      <div class='modal-header'>
        <h5 class='modal-title'>Tambah Pembeli</h5>
        <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
      </div>
      <div class='modal-body'>
        <form method='post' onsubmit="document.querySelector('button[name=tambah]').innerHTML='Loading...';">
          <div class='mb-3'>
            <label class='form-label'>Nama Pembeli</label>
            <input class='form-control' name='nama_pembeli' placeholder='Nama Pembeli' required>
          </div>
          <div class='mb-3'>
            <label class='form-label'>Alamat</label>
            <input class='form-control' name='alamat' placeholder='Alamat' required>
          </div>
          <button type='submit' class='btn btn-primary' name='tambah'>Tambah</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal Edit Pembeli -->
<div class='modal fade' id='modalEdit' tabindex='-1'>
  <div class='modal-dialog'>
    <div class='modal-content'>
      <div class='modal-header'>
        <h5 class='modal-title'>Edit Pembeli</h5>
        <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
      </div>
      <div class='modal-body'>
        <form method='post' onsubmit="document.querySelector('button[name=update]').innerHTML='Loading...';">
          <input type='hidden' name='id_pembeli' id='edit_id_pembeli'>
          <div class='mb-3'>
            <label class='form-label'>Nama Pembeli</label>
            <input class='form-control' name='nama_pembeli' id='edit_nama_pembeli' placeholder='Nama Pembeli' required>
          </div>
          <div class='mb-3'>
            <label class='form-label'>Alamat</label>
            <input class='form-control' name='alamat' id='edit_alamat' placeholder='Alamat' required>
          </div>
          <button type='submit' class='btn btn-success' name='update'>Update</button>
        </form>
      </div>
    </div>
  </div>
</div>



<!-- Modal Hapus -->
<div class='modal fade' id='modalHapus' tabindex='-1'>
  <div class='modal-dialog'>
    <div class='modal-content'>
      <div class='modal-header'>
        <h5 class='modal-title'>Konfirmasi Hapus</h5>
        <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
      </div>
      <div class='modal-body'>
        Yakin ingin menghapus pembeli ini?
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
