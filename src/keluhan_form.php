<?php
// Halaman form keluhan dan saran untuk user

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/db/db_init.php";

use src\adapter\feedback_repository\MysqlFeedbackRepository;
use src\adapter\user_repository\FakeUserRepository;
use src\adapter\user_repository\MysqlUserRepository;
use src\adapter\user_session\PhpUserSession;
use src\entity\Feedback;
use src\entity\FeedbackCategory;
use src\entity\FeedbackType;
use src\service\FeedbackService;


$user = new FakeUserRepository();
$session = new PhpUserSession($user);

$feedback = new MysqlFeedbackRepository($db);
$service = new FeedbackService($feedback, $session);

$judul = 'Form Keluhan dan Saran';

// --- Label mapping (Indonesian UI -> English enum) ---
$jenisMap = [
    'Keluhan'  => FeedbackType::Complaint,
    'Saran'    => FeedbackType::Suggestion,
];
$kategoriMap = [
    'Pelayanan'   => FeedbackCategory::Service,
    'Rasa menu'   => FeedbackCategory::MenuTaste,
    'Kebersihan'  => FeedbackCategory::Cleanliness,
    'Waktu tunggu' => FeedbackCategory::WaitingTime,
    'Lainnya'     => FeedbackCategory::Other,
];

$sent = false;
$error = null;
$oldNama = '';
$oldNoWa = '';
$oldIsi = '';
$oldJenis = 'Keluhan';
$oldKategori = 'Pelayanan';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $noWa = trim($_POST['no_wa'] ?? '');
    $jenisLabel = $_POST['jenis'] ?? '';
    $kategoriLabel = $_POST['kategori'] ?? '';
    $isi = trim($_POST['isi'] ?? '');

    $oldNama = $nama;
    $oldNoWa = $noWa;
    $oldIsi = $isi;
    $oldJenis = $jenisLabel ?: 'Keluhan';
    $oldKategori = $kategoriLabel ?: 'Pelayanan';

    if ($isi === '') {
        $error = 'Isi keluhan atau saran tidak boleh kosong.';
    } elseif (!isset($jenisMap[$jenisLabel])) {
        $error = 'Jenis tidak valid.';
    } elseif (!isset($kategoriMap[$kategoriLabel])) {
        $error = 'Kategori tidak valid.';
    } else {
        $feedbackEntity = new Feedback($isi, $jenisMap[$jenisLabel]);
        $feedbackEntity->withPhoneNumber($noWa !== '' ? $noWa : null);
        $feedbackEntity->withCategory($kategoriMap[$kategoriLabel]);

        $service->addFeedback($feedbackEntity);

        header('Location: keluhan_form.php?sent=1');
        exit;
    }
}

if (isset($_GET['sent']) && $_GET['sent'] === '1') {
    $sent = true;
}

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

            <?php if ($sent): ?>
                <p class="note" style="color:#15803d; font-weight:bold; margin-bottom:18px">
                    Terima kasih, masukanmu telah diterima kami.
                </p>
            <?php elseif ($error !== null): ?>
                <p class="note" style="color:#b91c1c; font-weight:bold; margin-bottom:18px">
                    <?= htmlspecialchars($error) ?>
                </p>
            <?php else: ?>
                <p class="note" style="margin-bottom:18px">Ceritakan pengalamanmu di SUNA. Masukanmu langsung diterima tim kami.</p>
            <?php endif ?>

            <form class="card" method="post" action="#">
                <label for="nama">Nama panggilan</label>
                <input id="nama" name="nama" maxlength="40" placeholder="Contoh: Dimas"
                    value="<?= htmlspecialchars($oldNama) ?>">

                <label for="no_wa">Nomor WhatsApp (opsional)</label>
                <input id="no_wa" name="no_wa" inputmode="numeric" maxlength="16" placeholder="08xxxxxxxxxx"
                    value="<?= htmlspecialchars($oldNoWa) ?>">
                <p class="note">Dipakai hanya untuk menghubungi balik soal keluhanmu.</p>

                <label for="jenis">Jenis</label>
                <select id="jenis" name="jenis">
                    <option value="Keluhan" <?= $oldJenis === 'Keluhan' ? 'selected' : '' ?>>Keluhan</option>
                    <option value="Saran" <?= $oldJenis === 'Saran' ? 'selected' : '' ?>>Saran</option>
                </select>

                <label for="kategori">Kategori</label>
                <select id="kategori" name="kategori">
                    <option value="Pelayanan" <?= $oldKategori === 'Pelayanan' ? 'selected' : '' ?>>Pelayanan</option>
                    <option value="Rasa menu" <?= $oldKategori === 'Rasa menu' ? 'selected' : '' ?>>Rasa menu</option>
                    <option value="Kebersihan" <?= $oldKategori === 'Kebersihan' ? 'selected' : '' ?>>Kebersihan</option>
                    <option value="Waktu tunggu" <?= $oldKategori === 'Waktu tunggu' ? 'selected' : '' ?>>Waktu tunggu</option>
                    <option value="Lainnya" <?= $oldKategori === 'Lainnya' ? 'selected' : '' ?>>Lainnya</option>
                </select>

                <label for="isi">Isi keluhan atau saran</label>
                <textarea id="isi" name="isi" maxlength="500" required><?= htmlspecialchars($oldIsi) ?></textarea>

                <button class="btn" type="submit">Kirim masukan</button>
            </form>
        </main>

    </div>
</body>

</html>
