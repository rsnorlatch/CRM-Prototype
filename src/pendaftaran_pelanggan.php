<?php /* Pendaftaran member cepat (tujuan QR di booth). Data disimpan ke Supabase lewat assets/js/pendaftaran.js */ ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Pelanggan SUNA</title>
    <link rel="stylesheet" href="assets/css/pendaftaran.css">
    <script type="module" src="assets/js/pendaftaran.js"></script>
</head>
<body>
<div class="container">
    <header class="brand">
        <div class="logo">S</div>
        <div><strong>SUNA</strong><small>Susu Sarjana</small></div>
    </header>
    <main class="content">
        <section class="intro">
            <span class="eyebrow">MEMBER SUNA</span>
            <h1>Jadi bagian dari <span>keluarga SUNA.</span></h1>
            <p>Daftarkan dirimu sebagai pelanggan untuk menikmati pengalaman layanan SUNA yang lebih mudah.</p>
        </section>
        <section class="form-card">
            <div id="reg-success" hidden>
                <h2>Pendaftaran berhasil!</h2>
                <p>Terima kasih telah bergabung dengan SUNA.</p>
                <a href="pendaftaran_pelanggan.php" class="button">Daftar pelanggan lain</a>
            </div>
            <div id="reg-box">
                <h2>Buat akun member</h2>
                <p>Isi informasi berikut dengan benar.</p>
                <div id="reg-error" class="error" hidden></div>
                <form id="reg-form">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" placeholder="Contoh: dimas123" minlength="3" maxlength="50" required>
                    <label for="no_wa">Nomor WhatsApp *</label>
                    <input type="tel" id="no_wa" name="no_wa" placeholder="Contoh: 081234567890" required>
                    <label class="consent">
                        <input type="checkbox" name="persetujuan_pdp" value="1" required>
                        <span>Saya menyetujui data username dan nomor WhatsApp saya disimpan dan digunakan oleh SUNA untuk keperluan pendaftaran serta layanan pelanggan.</span>
                    </label>
                    <button type="submit" class="button">Daftar sebagai pelanggan →</button>
                    <small>* Wajib diisi</small>
                </form>
            </div>
        </section>
    </main>
    <footer>© <?= date("Y") ?> SUNA — Susu Sarjana</footer>
</div>
</body>
</html>
