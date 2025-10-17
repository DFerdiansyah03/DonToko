<?php
if(!isset($page)) $page='';
?>
<div class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <h4>TOKO DONNY</h4>
    <div>
      <button class="sidebar-toggle" id="sidebar-toggle">☰</button>

    </div>
  </div>
  <ul class="sidebar-menu" id="sidebar-menu">
    <li><a href="dashboard.php" class="nav-link <?php echo $page=='dashboard'?'active':'';?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
    <li><a href="barang.php" class="nav-link <?php echo $page=='barang'?'active':'';?>"><i class="fas fa-box"></i> Barang</a></li>
    <li><a href="pembeli.php" class="nav-link <?php echo $page=='pembeli'?'active':'';?>"><i class="fas fa-users"></i> Pembeli</a></li>
    <li><a href="transaksi.php" class="nav-link <?php echo $page=='transaksi'?'active':'';?>"><i class="fas fa-shopping-cart"></i> Transaksi</a></li>
  </ul>
</div>
