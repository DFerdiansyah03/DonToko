<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'config/koneksi.php';
if (!$koneksi) {
    die("Connection failed: " . mysqli_connect_error());
}
$page='transaksi';

// Tambah
if(isset($_POST['tambah'])){
    $id_pembeli = intval($_POST['id_pembeli']);
    $id_barang = intval($_POST['id_barang']);
    $jumlah = intval($_POST['jumlah']);

    if($id_barang == 0 || $jumlah <= 0){
        $msg = 'Data tidak lengkap';
    } else {
        $q = mysqli_query($koneksi, "SELECT harga, stok, nama_barang FROM barang WHERE id_barang=$id_barang");
        $b = mysqli_fetch_assoc($q);
        $stok = intval($b['stok']);
        $harga = floatval($b['harga']);

        if($jumlah > $stok){
            $msg = 'Jumlah melebihi stok ' . $b['nama_barang'];
        } else {
            $total = $jumlah * $harga;
            $result1 = mysqli_query($koneksi, "INSERT INTO transaksi (id_pembeli,id_barang,jumlah,total_harga) VALUES ($id_pembeli,$id_barang,$jumlah,$total)");
            $result2 = mysqli_query($koneksi, "UPDATE barang SET stok = stok - $jumlah WHERE id_barang=$id_barang");
            if ($result1 && $result2) {
                $msg = 'Transaksi berhasil ditambahkan';
                header('Location: transaksi.php'); exit;
            } else {
                $msg = 'Error: ' . mysqli_error($koneksi);
            }
        }
    }
}

// Hapus
if(isset($_GET['hapus'])){
    $id = intval($_GET['hapus']);
    $d = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT id_barang,jumlah FROM transaksi WHERE id_transaksi=$id"));
    mysqli_query($koneksi, "DELETE FROM transaksi WHERE id_transaksi=$id") or die(mysqli_error($koneksi));
    mysqli_query($koneksi, "UPDATE barang SET stok = stok + {$d['jumlah']} WHERE id_barang={$d['id_barang']}") or die(mysqli_error($koneksi));
    header('Location: transaksi.php'); exit;
}

$transaksi = mysqli_query($koneksi, "SELECT t.id_transaksi, p.nama_pembeli, b.nama_barang, t.jumlah, t.total_harga, t.tanggal FROM transaksi t JOIN pembeli p ON t.id_pembeli=p.id_pembeli JOIN barang b ON t.id_barang=b.id_barang ORDER BY t.id_transaksi DESC");
$pembeli = mysqli_query($koneksi, "SELECT * FROM pembeli"); $barang = mysqli_query($koneksi, "SELECT * FROM barang");
?>
<!doctype html>
<html lang='en'>
<head>
<meta charset='utf-8'>
<title>Transaksi - Toko Donny</title>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel='stylesheet' href='assets/css/style.css'>
<style>
@media print {
  @page { size: A4 portrait; margin: 1cm; }
  html, body { height: 100%; margin: 0; padding: 0; page-break-after: avoid; }
  body * { visibility: hidden; }
  #receipt-content, #receipt-content * { visibility: visible; }
  #receipt-content {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    max-width: 80mm;
    margin: 0 auto;
    padding: 20px 10px;
    font-family: monospace;
    font-size: 12px;
    text-align: center;
    border: 1px solid #000;
    box-sizing: border-box;
    page-break-inside: avoid;
    page-break-before: avoid;
    page-break-after: avoid;
    height: auto;
    max-height: none;
  }
  .receipt-header {
    font-weight: bold;
    font-size: 16px;
    margin-bottom: 10px;
    text-align: center;
  }
  .receipt-footer {
    font-size: 10px;
    margin-top: 20px;
    font-style: italic;
    text-align: center;
  }
  #receipt-content p { margin: 5px 0; line-height: 1.2; }
  #receipt-content p:first-child { font-weight: bold; font-size: 14px; }
  .modal-dialog { width: auto !important; max-width: 80mm !important; margin: 0 auto !important; }
  .modal-content { border: none !important; box-shadow: none !important; }
  .modal-header, .modal-footer, .modal-backdrop { visibility: hidden !important; display: none !important; }
  body > *:not(.modal) { visibility: hidden !important; display: none !important; }
}
</style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class='main-content'>
<div class='container'>
  <div class='breadcrumb'><a href='dashboard.php'>Dashboard</a> > Transaksi</div>
  <h3>Transaksi</h3>

  <?php if(isset($msg)){ echo "<div style='background: #fff3cd; color: #856404; padding: 10px; border-radius: 4px; margin-bottom: 20px;'>$msg</div>"; } ?>

  <div class='mb-3'>
    <button class='btn btn-primary' data-bs-toggle='modal' data-bs-target='#modalTambah'>Tambah Transaksi</button>
  </div>

  <div class='card'>
    <div class='card-body'>
      <h5 class='card-title'>Daftar Transaksi</h5>
      <div class='mb-3'><input type='text' id='search-transaksi' placeholder='Cari transaksi...' class='form-control'></div>
      <div class='table-wrap'>
      <table class='table'>
        <thead><tr><th>ID</th><th>Pembeli</th><th>Barang</th><th>Jumlah</th><th>Total</th><th>Tanggal</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php while($r = mysqli_fetch_assoc($transaksi)){ ?>
          <tr>
            <td><?= $r['id_transaksi'] ?></td>
            <td><?= $r['nama_pembeli'] ?></td>
            <td><?= $r['nama_barang'] ?></td>
            <td><?= $r['jumlah'] ?></td>
            <td>Rp <?= number_format($r['total_harga'],2,',','.') ?></td>
            <td><?= $r['tanggal'] ?></td>
            <td><button class='btn btn-info' onclick='printReceipt(<?= $r['id_transaksi'] ?>, "<?= $r['nama_pembeli'] ?>", "<?= $r['nama_barang'] ?>", <?= $r['jumlah'] ?>, <?= $r['total_harga'] ?>, "<?= $r['tanggal'] ?>")'>Cetak</button> <a href='javascript:confirmDelete("transaksi.php?hapus=<?= $r['id_transaksi'] ?>")' class='btn btn-danger'>Hapus</a></td>
          </tr>
        <?php } ?>
        </tbody>
      </table>
      </div>
    </div>
  </div>

<!-- Modal Tambah Transaksi -->
<div class='modal fade' id='modalTambah' tabindex='-1'>
  <div class='modal-dialog'>
    <div class='modal-content'>
      <div class='modal-header'>
        <h5 class='modal-title'>Tambah Transaksi</h5>
        <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
      </div>
      <div class='modal-body'>
        <form method='post' onsubmit="document.querySelector('button[name=tambah]').innerHTML='Loading...';">
          <div class='mb-3'>
            <label class='form-label'>Pembeli</label>
            <select name='id_pembeli' class='form-control' required>
              <option value=''>-- Pilih Pembeli --</option>
              <?php mysqli_data_seek($pembeli, 0); while($p = mysqli_fetch_assoc($pembeli)){ ?>
                <option value='<?= $p['id_pembeli'] ?>'><?= $p['nama_pembeli'] ?></option>
              <?php } ?>
            </select>
          </div>
          <div class='mb-3'>
            <label class='form-label'>Barang</label>
            <select name='id_barang' class='form-control' required onchange='updateMaxJumlah(this.value)'>
              <option value=''>-- Pilih Barang --</option>
              <?php mysqli_data_seek($barang, 0); while($b = mysqli_fetch_assoc($barang)){ ?>
                <option value='<?= $b['id_barang'] ?>' data-stok='<?= $b['stok'] ?>'><?= $b['nama_barang'] ?> (stok: <?= $b['stok'] ?>)</option>
              <?php } ?>
            </select>
          </div>
          <div class='mb-3'>
            <label class='form-label'>Jumlah</label>
            <input type='number' name='jumlah' id='jumlah' class='form-control' placeholder='Jumlah' min='1' required>
          </div>
          <button class='btn btn-primary' name='tambah'>Simpan</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal Receipt -->
<div class='modal fade' id='modalReceipt' tabindex='-1'>
  <div class='modal-dialog'>
    <div class='modal-content'>
      <div class='modal-header'>
        <h5 class='modal-title'>Receipt</h5>
        <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
      </div>
      <div class='modal-body' id='receipt-content'>
        <!-- filled by JS -->
      </div>
      <div class='modal-footer'>
        <button type='button' class='btn btn-primary' onclick='window.print()'>Cetak</button>
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
        Yakin ingin menghapus transaksi ini?
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
<script>
function updateMaxJumlah(id) {
  const option = document.querySelector(`option[value='${id}']`);
  const stok = option ? option.getAttribute('data-stok') : 0;
  document.getElementById('jumlah').max = stok;
}
</script>
</body>
</html>
