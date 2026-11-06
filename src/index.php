<?php
$currentPage = 'home';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#153b35">
    <title>SUNA — Hub Pelanggan</title>
    <link rel="stylesheet" href="assets/css/pelanggan.css">
    <script type="module" src="assets/js/crm.js"></script>
</head>
<body data-page="home">
<div class="app">
    <?php require __DIR__ . '/includes/nav.php'; ?>
    <main>
        <section class="hero">
            <p class="eyebrow">CRM &amp; LAYANAN PELANGGAN SUNA</p>
            <h1>Pesan lebih awal.<br><em>Nikmati tanpa antre.</em></h1>
            <p>Temukan menu SUNA, atur waktu pengambilan, dan pantau pesanan dalam satu tempat.</p>
            <a class="btn" href="../index.html#preorder">Buat pre-order <span>→</span></a>
            <a class="btn btn-ghost" href="#auth">Masuk / daftar</a>
        </section>
        <section class="auth-layout" id="auth">
            <div>
                <p class="eyebrow">MULAI DI SINI</p>
                <h2>Selamat datang di SUNA.</h2>
                <p>Masuk untuk membuat pre-order, atau daftar menjadi pelanggan SUNA. Untuk membuat akun melalui QR, bagikan tautan halaman ini dengan tambahan <code>?source=qr</code>.</p>
                <p id="qr-notice" class="notice" hidden>Pendaftaran dari QR SUNA. Data profil akan tersimpan sebagai member.</p>
                <form id="consent-form" class="consent-form" hidden>
                    <label class="check-row"><input name="whatsapp_consent" type="checkbox"> Saya bersedia menerima promosi SUNA melalui WhatsApp.</label>
                    <button class="btn btn-outline" type="submit">Simpan preferensi promo</button>
                </form>
            </div>
            <section class="card auth-card">
                <div class="tabs">
                    <button class="auth-tab active" data-auth="login" type="button">Masuk</button>
                    <button class="auth-tab" data-auth="signup" type="button">Daftar member</button>
                </div>
                <form id="login-form" class="stack">
                    <label>Email<input name="email" type="email" autocomplete="email" required></label>
                    <label>Kata sandi<input name="password" type="password" autocomplete="current-password" minlength="8" required></label>
                    <button class="btn btn-wide" type="submit">Masuk</button>
                </form>
                <form id="signup-form" class="stack" hidden>
                    <label>Nama lengkap<input name="full_name" autocomplete="name" maxlength="100" required></label>
                    <label>Nomor WhatsApp<input name="phone" type="tel" autocomplete="tel" inputmode="tel" placeholder="+62 812 3456 7890" required></label>
                    <label>Email<input name="email" type="email" autocomplete="email" required></label>
                    <label>Kata sandi<input name="password" type="password" autocomplete="new-password" minlength="8" required></label>
                    <label class="check-row"><input name="whatsapp_consent" type="checkbox"> Saya bersedia menerima promosi SUNA melalui WhatsApp (opsional, dapat dicabut kapan saja).</label>
                    <button class="btn btn-wide" type="submit">Daftar member</button>
                </form>
                <p id="auth-feedback" class="feedback" aria-live="polite"></p>
                <p class="note">Gunakan data simulasi; jangan masukkan data pribadi pelanggan nyata untuk demo.</p>
            </section>
        </section>
        <footer>SUNA HUB PELANGGAN <span>Prototipe CRM · Data simulasi</span></footer>
    </main>
</div>
</body>
</html>
