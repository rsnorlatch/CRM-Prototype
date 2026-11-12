<?php
$currentPage = 'customers';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
