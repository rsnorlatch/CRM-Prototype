<?php
// Halaman form keluhan dan saran untuk user
$judul = 'Form Keluhan dan Saran';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($judul) ?> - SUNA CRM</title>
    <link rel="stylesheet" href="assets/css/pelanggan.css">
    <link rel="stylesheet" href="assets/css/keluhan.css">
</head>
<body>
<div class="app">

    <aside>
        <h1>SUNA (SUSU SARJANA)</h1>
        <a href="#">Dashboard</a>
        <a href="#">Katalog Menu</a>
        <a href="#">Pre-Order</a>
        <a href="#">Broadcast Promosi</a>
        <a href="pelanggan.php">Data Pelanggan</a>
        <a href="keluhan_admin.php">Keluhan &amp; Saran</a>
        <a href="keluhan_form.php" class="on">Form Keluhan (User)</a>
    </aside>

    <main>
        <h2>Keluhan dan saran</h2>
        <p class="note" style="margin-bottom:18px">Ceritakan pengalamanmu di SUNA. Masukanmu langsung diterima tim kami.</p>

        <form class="card" method="post" action="#">
            <label for="nama">Nama panggilan</label>
            <input id="nama" name="nama" maxlength="40" placeholder="Contoh: Dimas">

            <label for="no_wa">Nomor WhatsApp (opsional)</label>
            <input id="no_wa" name="no_wa" inputmode="numeric" maxlength="16" placeholder="08xxxxxxxxxx">
            <p class="note">Dipakai hanya untuk menghubungi balik soal keluhanmu.</p>

            <label for="jenis">Jenis</label>
            <select id="jenis" name="jenis">
                <option>Keluhan</option>
                <option>Saran</option>
            </select>

            <label for="kategori">Kategori</label>
            <select id="kategori" name="kategori">
                <option>Pelayanan</option>
                <option>Rasa menu</option>
                <option>Kebersihan</option>
                <option>Waktu tunggu</option>
                <option>Lainnya</option>
            </select>

            <label for="isi">Isi keluhan atau saran</label>
            <textarea id="isi" name="isi" maxlength="500" required></textarea>

            <button class="btn" type="submit">Kirim masukan</button>
        </form>
    </main>

</div>
</body>
</html>