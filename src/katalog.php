<?php
$currentPage = 'catalog';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Katalog Menu - SUNA CRM</title>
    <link rel="stylesheet" href="assets/css/pelanggan.css">
    <script type="module" src="assets/js/crm.js"></script>
</head>
<body data-page="catalog">
<div class="app">
    <?php require __DIR__ . '/includes/nav.php'; ?>
    <main>
        <p class="eyebrow">S4 + T2 · KATALOG PRODUK</p>
        <h1>Katalog Menu</h1>
        <p class="subheading">Kelola menu yang tersedia untuk dipesan pelanggan.</p>
        <form id="menu-form" class="card menu-form">
            <input type="hidden" name="id">
            <label>Nama menu<input name="name" maxlength="100" required></label>
            <label>Harga (Rp)<input name="price" type="number" min="0" step="500" required></label>
            <label>Deskripsi<input name="description" maxlength="500"></label>
            <label class="check-row"><input name="is_available" type="checkbox" checked> Tersedia untuk dipesan</label>
            <button class="btn" type="submit">Simpan menu</button>
            <button id="cancel-menu-edit" class="btn btn-outline" type="button" hidden>Batal</button>
        </form>
        <p id="menu-feedback" class="feedback" aria-live="polite"></p>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Nama menu</th><th>Deskripsi</th><th>Harga</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody id="menu-rows"><tr><td colspan="5" class="empty">Memuat menu...</td></tr></tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>
