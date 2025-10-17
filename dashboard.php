<?php
include 'config/koneksi.php';
$page='dashboard';
$where=''; $label='';
if(isset($_GET['filter'])){
    $tgl_mulai = $_GET['tgl_mulai']; $tgl_selesai = $_GET['tgl_selesai'];
    if($tgl_mulai && $tgl_selesai){ $where = "WHERE t.tanggal BETWEEN '$tgl_mulai' AND '$tgl_selesai'"; $label = " Periode: $tgl_mulai s/d $tgl_selesai"; }
}
$dash = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(id_transaksi) AS total_transaksi, COALESCE(SUM(total_harga),0) AS total_pendapatan, (SELECT b.nama_barang FROM transaksi t2 JOIN barang b ON t2.id_barang=b.id_barang WHERE YEAR(t2.tanggal) = YEAR(CURDATE()) GROUP BY b.id_barang ORDER BY SUM(t2.jumlah) DESC LIMIT 1) AS barang_terlaris FROM transaksi WHERE YEAR(tanggal) = YEAR(CURDATE())"));

$lap = mysqli_query($koneksi, "SELECT t.id_transaksi, p.nama_pembeli, b.nama_barang, t.jumlah, t.total_harga, t.tanggal FROM transaksi t JOIN pembeli p ON t.id_pembeli=p.id_pembeli JOIN barang b ON t.id_barang=b.id_barang $where ORDER BY t.id_transaksi DESC");

$chart_where = $where ?: "WHERE YEAR(t.tanggal) = YEAR(CURDATE())";
if(isset($_GET['ajax'])){
    $chart_where = isset($_GET['tgl_mulai']) && isset($_GET['tgl_selesai']) ? "WHERE t.tanggal BETWEEN '{$_GET['tgl_mulai']}' AND '{$_GET['tgl_selesai']}'" : "WHERE YEAR(t.tanggal) = YEAR(CURDATE())";
}
$chart = mysqli_query($koneksi, "SELECT DATE(t.tanggal) as tanggal, COUNT(*) as jumlah_transaksi FROM transaksi t $chart_where GROUP BY DATE(t.tanggal) ORDER BY tanggal");
$chart_labels = []; $chart_data = [];
while($r = mysqli_fetch_assoc($chart)){ $chart_labels[] = $r['tanggal']; $chart_data[] = $r['jumlah_transaksi']; }

if(isset($_GET['ajax'])){
    $sub_where = $chart_where ? str_replace('t.', 't2.', $chart_where) : '';
    $filtered_dash = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(t.id_transaksi) AS total_transaksi, COALESCE(SUM(t.total_harga),0) AS total_pendapatan, (SELECT b.nama_barang FROM transaksi t2 JOIN barang b ON t2.id_barang=b.id_barang $sub_where GROUP BY b.id_barang ORDER BY SUM(t2.jumlah) DESC LIMIT 1) AS barang_terlaris FROM transaksi t $chart_where"));
    $lap_where = $chart_where ? str_replace('t.', '', $chart_where) : '';
    $lap = mysqli_query($koneksi, "SELECT t.id_transaksi, p.nama_pembeli, b.nama_barang, t.jumlah, t.total_harga, t.tanggal FROM transaksi t JOIN pembeli p ON t.id_pembeli=p.id_pembeli JOIN barang b ON t.id_barang=b.id_barang $lap_where ORDER BY t.id_transaksi DESC");
    $table_rows = '';
    while($r = mysqli_fetch_assoc($lap)){
      $table_rows .= "<tr><td>{$r['id_transaksi']}</td><td>{$r['nama_pembeli']}</td><td>{$r['nama_barang']}</td><td>{$r['jumlah']}</td><td>Rp " . number_format($r['total_harga'],2,',','.') . "</td><td>{$r['tanggal']}</td></tr>";
    }
    $label = isset($_GET['tgl_mulai']) && isset($_GET['tgl_selesai']) ? " Periode: {$_GET['tgl_mulai']} s/d {$_GET['tgl_selesai']}" : '';
    header('Content-Type: application/json');
    echo json_encode([
        'labels' => $chart_labels,
        'data' => $chart_data,
        'total_transaksi' => $filtered_dash['total_transaksi'],
        'total_pendapatan' => $filtered_dash['total_pendapatan'],
        'barang_terlaris' => $filtered_dash['barang_terlaris'] ?? '-',
        'table_rows' => $table_rows,
        'label' => $label
    ]);
    exit;
}
?>
<!doctype html>
<html lang='en'>
<head>
<meta charset='utf-8'>
<title>Dashboard - Toko Donny</title>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel='stylesheet' href='assets/css/style.css'>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class='main-content'>
<div class='container'>
  <div class='breadcrumb'>Dashboard</div>
  <h3>Dashboard Penjualan</h3>

  <div class='card mb-3'>
    <div class='card-body'>
      <form method='get' class='row g-2 align-items-center'>
        <div class='col-auto'><label class='col-form-label'>Dari</label></div>
        <div class='col-auto'><input type='date' name='tgl_mulai' class='form-control' value='<?= $_GET['tgl_mulai'] ?? '' ?>'></div>
        <div class='col-auto'><label class='col-form-label'>Sampai</label></div>
        <div class='col-auto'><input type='date' name='tgl_selesai' class='form-control' value='<?= $_GET['tgl_selesai'] ?? '' ?>'></div>
        <div class='col-auto'><button type='button' id='filter-btn' class='btn btn-primary'>Tampilkan</button></div>
        <div class='col-auto'><button type='button' id='reset-btn' class='btn btn-secondary'>Reset</button></div>
        <div class='col-auto'><button type='button' class='btn btn-success' onclick='printReport()'>Cetak</button></div>
      </form>
    </div>
  </div>

  <div style='overflow-x: auto; width: 100%;'>
    <canvas id='chart' style='height: 400px; margin-bottom: 20px;'></canvas>
  </div>
  <div class='row'>
    <div class='col-4'>
      <div class='card card-summary mb-3'><div class='card-body'><h6>📊 Total Transaksi</h6><div class='value'><?= $dash['total_transaksi'] ?></div></div></div>
    </div>
    <div class='col-4'>
      <div class='card card-summary mb-3'><div class='card-body'><h6>💰 Total Pendapatan</h6><div class='value'>Rp <?= number_format($dash['total_pendapatan'],2,',','.') ?></div></div></div>
    </div>
    <div class='col-4'>
      <div class='card card-summary mb-3'><div class='card-body'><h6>🏆 Barang Terlaris</h6><div class='value'><?= $dash['barang_terlaris'] ?? '-' ?></div></div></div>
    </div>
  </div>

  <div class='row mb-3'>
    <div class='col-4'>
      <div class='card text-center'>
        <div class='card-body'>
          <h6><i class="fas fa-box"></i> Barang</h6>
          <p>Kelola daftar barang</p>
          <a href='barang.php' class='btn btn-primary'>Kelola Barang</a>
        </div>
      </div>
    </div>
    <div class='col-4'>
      <div class='card text-center'>
        <div class='card-body'>
          <h6><i class="fas fa-users"></i> Pembeli</h6>
          <p>Kelola data pelanggan</p>
          <a href='pembeli.php' class='btn btn-primary'>Kelola Pembeli</a>
        </div>
      </div>
    </div>
    <div class='col-4'>
      <div class='card text-center'>
        <div class='card-body'>
          <h6><i class="fas fa-shopping-cart"></i> Transaksi</h6>
          <p>Catat penjualan</p>
          <a href='transaksi.php' class='btn btn-primary'>Kelola Transaksi</a>
        </div>
      </div>
    </div>
  </div>

  <div class='card'>
    <div class='card-body'>
      <h5 class='card-title'>Laporan Penjualan <?= $label ?></h5>
      <div class='mb-3'><input type='text' id='search-laporan' placeholder='Cari laporan...' class='form-control'></div>
      <div class='table-wrap'>
      <table class='table'>
        <thead><tr><th>ID</th><th>Pembeli</th><th>Barang</th><th>Jumlah</th><th>Total</th><th>Tanggal</th></tr></thead>
        <tbody>
        <?php while($r = mysqli_fetch_assoc($lap)){ ?>
          <tr>
            <td><?= $r['id_transaksi'] ?></td>
            <td><?= $r['nama_pembeli'] ?></td>
            <td><?= $r['nama_barang'] ?></td>
            <td><?= $r['jumlah'] ?></td>
            <td>Rp <?= number_format($r['total_harga'],2,',','.') ?></td>
            <td><?= $r['tanggal'] ?></td>
          </tr>
        <?php } ?>
        </tbody>
      </table>
      </div>
    </div>
  </div>
</div>
</div>
<footer class='footer'>Dibuat untuk UTS - Toko Donny</footer>
<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/chart.js'></script>
<script src='https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2'></script>
<script src='assets/js/script.js'></script>
<script>
const ctx = document.getElementById('chart').getContext('2d');
const chart = new Chart(ctx, {
  type: 'line',
  data: {
    labels: <?php echo json_encode($chart_labels); ?>,
    datasets: [{
      label: 'Total Transaksi',
      data: <?php echo json_encode($chart_data); ?>,
      backgroundColor: 'rgba(54, 162, 235, 0.5)',
      borderColor: 'rgba(54, 162, 235, 1)',
      borderWidth: 1
    }]
  },
  options: {
    responsive: true,
    scales: {
      y: { beginAtZero: true, title: { display: true, text: 'Jumlah Transaksi' } },
      x: { title: { display: true, text: 'Tanggal' } }
    },
    plugins: {
      title: { display: true, text: 'Histogram Transaksi per Tanggal' },
      zoom: {
        zoom: {
          wheel: { enabled: false },
          pinch: { enabled: true },
          mode: 'xy'
        },
        pan: {
          enabled: true,
          mode: 'xy'
        }
      }
    }
  }
});

 </script>
<script>
document.getElementById('filter-btn').addEventListener('click', function() {
  const start = document.querySelector('input[name=tgl_mulai]').value;
  const end = document.querySelector('input[name=tgl_selesai]').value;
  if(!start || !end) return alert('Pilih tanggal mulai dan selesai');
  fetch('dashboard.php?ajax=1&tgl_mulai='+start+'&tgl_selesai='+end)
  .then(response => response.json())
  .then(data => {
    chart.data.labels = data.labels;
    chart.data.datasets[0].data = data.data;
    chart.update();
    document.querySelectorAll('.card-summary .value')[0].innerHTML = data.total_transaksi;
    document.querySelectorAll('.card-summary .value')[1].innerHTML = 'Rp ' + new Intl.NumberFormat('id-ID').format(data.total_pendapatan);
    document.querySelectorAll('.card-summary .value')[2].innerHTML = data.barang_terlaris;
    document.querySelector('h5.card-title').innerHTML = 'Laporan Penjualan ' + data.label;
    document.querySelector('.table tbody').innerHTML = data.table_rows;
  })
  .catch(error => console.error('Error:', error));
});
document.getElementById('reset-btn').addEventListener('click', function() {
  document.querySelector('input[name=tgl_mulai]').value = '';
  document.querySelector('input[name=tgl_selesai]').value = '';
  fetch('dashboard.php?ajax=1')
  .then(response => response.json())
  .then(data => {
    chart.data.labels = data.labels;
    chart.data.datasets[0].data = data.data;
    chart.update();
    document.querySelectorAll('.card-summary .value')[0].innerHTML = data.total_transaksi;
    document.querySelectorAll('.card-summary .value')[1].innerHTML = 'Rp ' + new Intl.NumberFormat('id-ID').format(data.total_pendapatan);
    document.querySelectorAll('.card-summary .value')[2].innerHTML = data.barang_terlaris;
    document.querySelector('h5.card-title').innerHTML = 'Laporan Penjualan ' + data.label;
    document.querySelector('.table tbody').innerHTML = data.table_rows;
  })
  .catch(error => console.error('Error:', error));
});
</script>
</body>
</html>
