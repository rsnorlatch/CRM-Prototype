<?php
$currentPage = 'feedback';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kirim Keluhan &amp; Saran - SUNA CRM</title>
    <link rel="stylesheet" href="assets/css/pelanggan.css">
    <script type="module" src="assets/js/crm.js"></script>
</head>
<body data-page="feedback">
<div class="app">
    <?php require __DIR__ . '/includes/nav.php'; ?>
    <main>
        <p class="eyebrow">W2 + T3 · LAYANAN PELANGGAN</p>
        <h1>Keluhan &amp; Saran</h1>
        <p class="subheading">Ceritakan pengalamanmu di SUNA. Masukanmu akan diterima tim kami.</p>
        <form id="feedback-form" class="card stack form-card">
            <label>Nama panggilan<input name="sender_name" maxlength="60" placeholder="Contoh: Dimas"></label>
            <label>Nomor WhatsApp (opsional)<input name="sender_phone" type="tel" inputmode="numeric" maxlength="16" placeholder="08xxxxxxxxxx"></label>
            <label>Jenis<select name="kind" required><option value="complaint">Keluhan</option><option value="suggestion">Saran</option></select></label>
            <label>Kategori<select name="category" required><option value="service">Pelayanan</option><option value="menu">Rasa menu</option><option value="cleanliness">Kebersihan</option><option value="waiting_time">Waktu tunggu</option><option value="other">Lainnya</option></select></label>
            <label>Isi keluhan atau saran<textarea name="message" maxlength="500" rows="5" required></textarea></label>
            <p class="note">Tanpa login pun bisa. Nomor WhatsApp hanya dipakai untuk menghubungi balik soal masukanmu.</p>
            <button class="btn" type="submit">Kirim masukan</button>
            <p id="feedback-result" class="feedback" aria-live="polite"></p>
        </form>
    </main>
</div>
</body>
</html>
