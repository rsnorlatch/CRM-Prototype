<?php
// Halaman Data Pelanggan
$judul = 'Data Pelanggan';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($judul) ?> - SUNA CRM</title>
    <link rel="stylesheet" href="assets/css/pelanggan.css">
</head>
<body>
<div class="app">

    <aside>
        <h1>SUNA (SUSU SARJANA)</h1>
        <a href="#">Dashboard</a>
        <a href="#">Katalog Menu</a>
        <a href="#">Pre-Order</a>
        <a href="#">Broadcast Promosi</a>
        <a href="pelanggan.php" class="on">Data Pelanggan</a>
        <a href="keluhan_admin.php">Keluhan &amp; Saran</a>
        <a href="keluhan_form.php">Form Keluhan (User)</a>
    </aside>

    <main>
        <h2>Data Pelanggan</h2>

        <div class="bar">
            <input type="search" placeholder="Cari pengguna">
            <button class="btn">Tambah pengguna</button>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Pengguna</th>
                    <th>No Pengguna</th>
                    <th>Segmen</th>
                    <th>Transaksi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="6" class="kosong">Belum ada data pelanggan.</td>
                </tr>
            </tbody>
        </table>

        <p class="info">Menampilkan data pelanggan</p>
    </main>

</div>
</body>
</html>