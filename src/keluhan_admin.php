<?php
$currentPage = 'complaints';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Keluhan &amp; Saran - SUNA CRM</title>
    <link rel="stylesheet" href="assets/css/pelanggan.css">
    <script type="module" src="assets/js/crm.js"></script>
</head>
<body data-page="complaints">
<div class="app">
    <?php require __DIR__ . '/includes/nav.php'; ?>
    <main>
        <p class="eyebrow">W2 + T3 · TICKETING KELUHAN</p>
        <h1>Keluhan &amp; Saran</h1>
        <p class="subheading">Tentukan PIC, pantau status penanganan, dan tindak lanjuti masukan member.</p>
        <section class="stats">
            <article><b id="count-new">0</b><span>Baru</span></article>
            <article><b id="count-progress">0</b><span>Diproses</span></article>
            <article><b id="count-done">0</b><span>Selesai</span></article>
        </section>
        <div class="toolbar">
            <select id="complaint-filter" aria-label="Filter status">
                <option value="all">Semua status</option><option value="new">Baru</option><option value="in_progress">Diproses</option><option value="resolved">Selesai</option>
            </select>
            <button id="refresh-complaints" class="btn btn-outline" type="button">Muat ulang</button>
            <a href="keluhan_form.php" class="btn">Tambah masukan</a>
        </div>
        <p id="complaints-feedback" class="feedback" aria-live="polite"></p>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Tiket</th><th>Masuk</th><th>Pengirim</th><th>Jenis / kategori</th><th>Isi</th><th>PIC</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody id="complaint-rows"><tr><td colspan="8" class="empty">Memuat masukan...</td></tr></tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>
