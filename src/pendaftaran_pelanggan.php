<<<<<<< HEAD
<?php /* Pendaftaran member cepat (tujuan QR di booth). Data disimpan ke Supabase lewat assets/js/pendaftaran.js */ ?>
=======

<?php
require_once __DIR__ . "/db/db_init.php";

$success = isset($_GET["registered"]);
$error = "";
$username = "";
$noWa = "";

// Membuat tabel pelanggan jika belum tersedia
$db->query("
CREATE TABLE IF NOT EXISTS pelanggan (
    id_pelanggan INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    no_wa VARCHAR(20) NOT NULL UNIQUE,
    persetujuan_pdp TINYINT(1) NOT NULL DEFAULT 0,
    tanggal_daftar TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

// Proses pendaftaran
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $noWa = preg_replace('/[\s\-().]/', '', trim($_POST["no_wa"] ?? ""));
    $consent = isset($_POST["persetujuan_pdp"]);

    if (strlen($username) < 3 || strlen($username) > 50) {
        $error = "Username harus 3-50 karakter.";
    } elseif (!preg_match('/^(?:\+?62|0)[0-9]{8,14}$/', $noWa)) {
        $error = "Format nomor WhatsApp tidak valid.";
    } elseif (!$consent) {
        $error = "Persetujuan wajib dicentang.";
    } else {
        if (str_starts_with($noWa, "+62")) {
            $noWa = "62" . substr($noWa, 3);
        } elseif (str_starts_with($noWa, "0")) {
            $noWa = "62" . substr($noWa, 1);
        }

        $stmt = $db->prepare(
            "INSERT INTO pelanggan
            (username, no_wa, persetujuan_pdp)
            VALUES (?, ?, 1)"
        );

        $stmt->bind_param("ss", $username, $noWa);

        if ($stmt->execute()) {
            header("Location: pendaftaran_pelanggan.php?registered=1");
            exit;
        } else {
            $error = $stmt->errno == 1062
                ? "Nomor WhatsApp sudah terdaftar."
                : "Pendaftaran gagal. Silakan coba lagi.";
        }

        $stmt->close();
    }
}
?>

>>>>>>> e367da03842d43393a0aacc9fc3c94a48b45e465
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Pelanggan SUNA</title>
    <link rel="stylesheet" href="assets/css/pendaftaran.css">
<<<<<<< HEAD
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
=======
</head>
<body>

<div class="container">

    <header class="brand">
        <div class="logo">S</div>
        <div>
            <strong>SUNA</strong>
            <small>Susu Sarjana</small>
        </div>
    </header>

    <main class="content">

        <section class="intro">
            <span class="eyebrow">MEMBER SUNA</span>

            <h1>
                Jadi bagian dari
                <span>keluarga SUNA.</span>
            </h1>

            <p>
                Daftarkan dirimu sebagai pelanggan
                untuk menikmati pengalaman layanan
                SUNA yang lebih mudah.
            </p>
        </section>

        <section class="form-card">

            <?php if ($success): ?>

                <h2>Pendaftaran berhasil!</h2>
                <p>Terima kasih telah bergabung dengan SUNA.</p>

                <a href="pendaftaran_pelanggan.php" class="button">
                    Daftar pelanggan lain
                </a>

            <?php else: ?>

                <h2>Buat akun member</h2>
                <p>Isi informasi berikut dengan benar.</p>

                <?php if ($error): ?>
                    <div class="error">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST">

                    <label for="username">Username *</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Contoh: dimas123"
                        minlength="3"
                        maxlength="50"
                        value="<?= htmlspecialchars($username) ?>"
                        required
                    >

                    <label for="no_wa">Nomor WhatsApp *</label>
                    <input
                        type="tel"
                        id="no_wa"
                        name="no_wa"
                        placeholder="Contoh: 081234567890"
                        value="<?= htmlspecialchars($noWa) ?>"
                        required
                    >

                    <label class="consent">
                        <input
                            type="checkbox"
                            name="persetujuan_pdp"
                            value="1"
                            required
                        >
                        <span>
                            Saya menyetujui data username dan
                            nomor WhatsApp saya disimpan dan
                            digunakan oleh SUNA untuk keperluan
                            pendaftaran serta layanan pelanggan.
                        </span>
                    </label>

                    <button type="submit" class="button">
                        Daftar sebagai pelanggan →
                    </button>

                    <small>* Wajib diisi</small>

                </form>

            <?php endif; ?>

        </section>

    </main>

    <footer>
        © <?= date("Y") ?> SUNA — Susu Sarjana
    </footer>

</div>

>>>>>>> e367da03842d43393a0aacc9fc3c94a48b45e465
</body>
</html>
