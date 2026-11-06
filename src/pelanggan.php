<?php
<<<<<<< HEAD
$currentPage = 'customers';
=======
// Halaman Data Pelanggan
$judul = 'Data Pelanggan';
>>>>>>> e367da03842d43393a0aacc9fc3c94a48b45e465
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
<<<<<<< HEAD
    <title>Data Pelanggan - SUNA CRM</title>
    <link rel="stylesheet" href="assets/css/pelanggan.css">
    <script type="module" src="assets/js/crm.js"></script>
</head>
<body data-page="customers">
<div class="app">
    <?php require __DIR__ . '/includes/nav.php'; ?>
    <main>
        <p class="eyebrow">W1 + O3 · DATA MEMBER DIGITAL</p>
        <h1>Data Pelanggan</h1>
        <p class="subheading">Cari dan perbarui profil member. Nomor WhatsApp disamarkan pada tabel.</p>
        <section class="toolbar">
            <input id="customer-search" type="search" placeholder="Cari nama, email, atau username" aria-label="Cari pelanggan">
            <a class="btn" href="index.php?source=qr#auth">Tambah member</a>
            <button id="refresh-customers" class="btn btn-outline" type="button">Muat ulang</button>
        </section>
        <p id="customers-feedback" class="feedback" aria-live="polite"></p>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Nama</th><th>Email</th><th>WhatsApp</th><th>Persetujuan promo</th><th>Terdaftar</th><th>Aksi</th></tr></thead>
                <tbody id="customer-rows"><tr><td colspan="6" class="empty">Memuat data...</td></tr></tbody>
            </table>
        </div>
        <h2 style="margin-top:28px">Member pendaftaran cepat (QR)</h2>
        <p class="subheading">Dari <code>pendaftaran_pelanggan.php</code> — username, WhatsApp disamarkan, persetujuan PDP.</p>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Username</th><th>WhatsApp</th><th>Persetujuan PDP</th><th>Terdaftar</th></tr></thead>
                <tbody id="member-rows"><tr><td colspan="4" class="empty">Memuat data...</td></tr></tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>
=======
    <title><?= htmlspecialchars($judul) ?> - SUNA CRM</title>
    <link rel="stylesheet" href="assets/css/pelanggan.css">
</head>
<body>
<div class="app">

    <aside>
        <h1>SUNA (SUSU SARJANA)</h1>
        <a href="#">Dashboard</a>
        <a href="#">Katalog Menu</a>
        <a href="#">Pre-Order</a>
        <a href="#">Broadcast Promosi</a>
        <a href="pelanggan.php" class="on">Data Pelanggan</a>
        <a href="keluhan_admin.php">Keluhan &amp; Saran</a>
        <a href="keluhan_form.php">Form Keluhan (User)</a>
    </aside>

    <main>
        <h2>Data Pelanggan</h2>

        <div class="bar">
            <input type="search" placeholder="Cari pengguna">
            <button class="btn">Tambah pengguna</button>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Pengguna</th>
                    <th>No Pengguna</th>
                    <th>Segmen</th>
                    <th>Transaksi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="6" class="kosong">Belum ada data pelanggan.</td>
                </tr>
            </tbody>
        </table>

        <p class="info">Menampilkan data pelanggan</p>
    </main>

</div>
</body>
</html>
>>>>>>> e367da03842d43393a0aacc9fc3c94a48b45e465
