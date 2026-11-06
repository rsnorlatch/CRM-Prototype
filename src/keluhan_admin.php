<?php
<<<<<<< HEAD
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
=======
// Halaman admin untuk melihat keluhan dan saran


require_once __DIR__ . "/../vendor/autoload.php";

use src\adapter\feedback_repository\FakeFeedbackRepository;
use src\adapter\feedback_repository\MysqlFeedbackRepository;
use src\adapter\user_repository\FakeUserRepository;
use src\adapter\user_repository\MysqlUserRepository;
use src\adapter\user_session\FakeUserSession;
use src\adapter\user_session\PhpUserSession;
use src\entity\Feedback;
use src\entity\User;
use src\entity\FeedbackStatus;
use src\entity\FeedbackType;
use src\entity\FeedbackCategory;
use src\service\FeedbackService;

$judul = 'Keluhan & Saran';

$db = new mysqli("localhost", "root", "", "crm_prototype");
$user = new FakeUserRepository();
$logged_in_user = (new User("user1", "password"))->withId(1);
$user->insert($logged_in_user);

$session = new PhpUserSession($user);
$session->setCurrentUser($logged_in_user);

$feedbacks = new MysqlFeedbackRepository($db);

if (count($feedbacks->getAll()) == 0)
    $feedbacks->insert(new Feedback("feedback1", FeedbackType::Complaint));

$service = new FeedbackService($feedbacks, $session);

// --- Action handlers (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if ($id !== false && $id !== null) {
        if (isset($_POST['set_pic'])) {
            $pic = $_POST['pic'] ?? '';
            $service->setPIC($id, $pic);
        } elseif (isset($_POST['status_action'])) {
            $action = $_POST['status_action'];
            if ($action === 'Selesai') {
                $service->markFeedbackAsResolved($id);
            } elseif ($action === 'Diproses') {
                $service->makrFeedbackAsProcessed($id);
            }
            // 'Baru' has no write method in the service; intentionally no-op
        }
    }
    $query = '';
    if (!empty($_GET['status'])) {
        $query = '?status=' . urlencode($_GET['status']);
    }
    header('Location: keluhan_admin.php' . $query);
    exit;
}

// --- Filter ---
$filterStatus = $_GET['status'] ?? '';

// --- Data ---
$rawFeedbacks = $service->getFeedbackData();

// --- Summary counts (unfiltered totals) ---
$countBaru = 0;
$countDiproses = 0;
$countSelesai = 0;
foreach ($rawFeedbacks as $f) {
    if ($f->status === null) {
        continue;
    }
    switch ($f->status->value) {
        case 'Pending':
            $countBaru++;
            break;
        case 'Processed':
            $countDiproses++;
            break;
        case 'Resolved':
            $countSelesai++;
            break;
    }
}

$allFeedbacks = $rawFeedbacks;
if ($filterStatus !== '') {
    $statusMap = ['Baru' => 'Pending', 'Diproses' => 'Processed', 'Selesai' => 'Resolved'];
    $enumStatus = $statusMap[$filterStatus] ?? null;
    if ($enumStatus !== null) {
        $allFeedbacks = array_values(array_filter(
            $allFeedbacks,
            fn($f) => $f->status !== null && $f->status->value === $enumStatus
        ));
    }
}

// --- Label helpers (English enum -> Indonesian UI) ---
function statusLabel(?FeedbackStatus $status): string
{
    return match ($status) {
        FeedbackStatus::Pending   => 'Baru',
        FeedbackStatus::Processed => 'Diproses',
        FeedbackStatus::Resolved  => 'Selesai',
        default                   => '—',
    };
}

function typeLabel(?FeedbackType $type): string
{
    return match ($type) {
        FeedbackType::Complaint  => 'Keluhan',
        FeedbackType::Suggestion => 'Saran',
        default                  => '—',
    };
}

function categoryLabel(?FeedbackCategory $category): string
{
    return $category !== null ? $category->toString() : '—';
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
            <a href="keluhan_admin.php" class="on">Keluhan &amp; Saran</a>
            <a href="keluhan_form.php">Form Keluhan (User)</a>
        </aside>

        <main>
            <h2>Keluhan &amp; Saran</h2>

            <div class="sum">
                <div><b><?= $countBaru ?></b><span>Baru</span></div>
                <div><b><?= $countDiproses ?></b><span>Diproses</span></div>
                <div><b><?= $countSelesai ?></b><span>Selesai</span></div>
            </div>

            <form method="get" class="bar">
                <select name="status" onchange="this.form.submit()">
                    <option value="" <?= $filterStatus === '' ? 'selected' : '' ?>>Semua status</option>
                    <option value="Baru" <?= $filterStatus === 'Baru' ? 'selected' : '' ?>>Baru</option>
                    <option value="Diproses" <?= $filterStatus === 'Diproses' ? 'selected' : '' ?>>Diproses</option>
                    <option value="Selesai" <?= $filterStatus === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                </select>
            </form>

            <div class="wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Tiket</th>
                            <th>Masuk</th>
                            <th>Pengirim</th>
                            <th>Jenis</th>
                            <th>Kategori</th>
                            <th>Isi</th>
                            <th>PIC</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($allFeedbacks)): ?>
                            <tr>
                                <td colspan="8">Tidak ada data</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($allFeedbacks as $f): ?>
                                <tr>
                                    <td><?= htmlspecialchars('TK-' . str_pad($f->id, 3, '0', STR_PAD_LEFT)) ?></td>
                                    <td><?= htmlspecialchars($f->created_at->format('d/m H:i')) ?></td>
                                    <td><?= htmlspecialchars($f->phone_number ?? '—') ?></td>
                                    <td><?= htmlspecialchars(typeLabel($f->type)) ?></td>
                                    <td><?= htmlspecialchars(categoryLabel($f->category)) ?></td>
                                    <td><?= htmlspecialchars($f->feedback) ?></td>
                                    <td>
                                        <form method="post" style="display:inline">
                                            <input type="hidden" name="id" value="<?= $f->id ?>">
                                            <select name="pic" onchange="this.form.submit()">
                                                <?php foreach (['Rara', 'Faris', 'Febri', 'Nopal', 'Dimas'] as $pic): ?>
                                                    <option value="<?= htmlspecialchars($pic) ?>" <?= ($f->person_in_charge ?? '') === $pic ? 'selected' : '' ?>><?= htmlspecialchars($pic) ?></option>
                                                <?php endforeach ?>
                                            </select>
                                            <input type="hidden" name="set_pic" value="1">
                                        </form>
                                    </td>
                                    <td>
                                        <form method="post" style="display:inline">
                                            <input type="hidden" name="id" value="<?= $f->id ?>">
                                            <select name="status_action" onchange="this.form.submit()">
                                                <option value="Baru" <?= statusLabel($f->status) === 'Baru' ? 'selected' : '' ?>>Baru</option>
                                                <option value="Diproses" <?= statusLabel($f->status) === 'Diproses' ? 'selected' : '' ?>>Diproses</option>
                                                <option value="Selesai" <?= statusLabel($f->status) === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                                            </select>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>
        </main>

    </div>
</body>

>>>>>>> e367da03842d43393a0aacc9fc3c94a48b45e465
</html>
