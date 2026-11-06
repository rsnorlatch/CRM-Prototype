<?php
$currentPage = $currentPage ?? '';
?>
<aside class="sidebar">
    <a class="brand" href="index.php"><img src="assets/images/logo-suna.png" alt="Logo SUNA"><span>SUNA<small>SUSU SARJANA</small></span></a>
    <a class="<?= $currentPage === 'home' ? 'on' : '' ?>" href="index.php"><span class="menu-icon">⌂</span>Dashboard</a>
    <a class="<?= $currentPage === 'catalog' ? 'on' : '' ?>" href="katalog.php"><span class="menu-icon">▦</span>Katalog Menu</a>
    <a href="../index.html#preorder"><span class="menu-icon">◷</span>Pre-Order</a>
    <a href="../index.html#orders" data-admin-link hidden><span class="menu-icon">▤</span>Pesanan</a>
    <a href="../index.html#broadcast"><span class="menu-icon">✉</span>Broadcast Promosi</a>
    <a class="<?= $currentPage === 'customers' ? 'on' : '' ?>" href="pelanggan.php"><span class="menu-icon">♙</span>Data Pelanggan</a>
    <a class="<?= $currentPage === 'complaints' ? 'on' : '' ?>" href="keluhan_admin.php"><span class="menu-icon">☷</span>Keluhan &amp; Saran</a>
    <a class="<?= $currentPage === 'feedback' ? 'on' : '' ?>" href="keluhan_form.php"><span class="menu-icon">✎</span>Kirim Masukan</a>
    <a href="pendaftaran_pelanggan.php"><span class="menu-icon">＋</span>Pendaftaran Member</a>
    <div class="sidebar-account">
        <span id="account-name">Memeriksa sesi...</span>
        <button id="logout-button" class="logout-button" type="button" hidden>Keluar</button>
    </div>
</aside>
