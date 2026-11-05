<?php
// Halaman admin untuk melihat keluhan dan saran
$judul = 'Keluhan & Saran';
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
            <div><b>0</b><span>Baru</span></div>
            <div><b>0</b><span>Diproses</span></div>
            <div><b>0</b><span>Selesai</span></div>
        </div>

        <div class="bar">
            <select>
                <option>Semua status</option>
                <option>Baru</option>
                <option>Diproses</option>
                <option>Selesai</option>
            </select>
        </div>

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
                    <tr>
                        <td>TK-001</td>
                        <td>05/11 09:12</td>
                        <td>Contoh</td>
                        <td>Keluhan</td>
                        <td>Pelayanan</td>
                        <td>Contoh isi keluhan (data contoh).</td>
                        <td>
                            <select>
                                <option>Rara</option>
                                <option>Faris</option>
                                <option>Febri</option>
                                <option>Nopal</option>
                                <option>Dimas</option>
                            </select>
                        </td>
                        <td>
                            <select>
                                <option>Baru</option>
                                <option>Diproses</option>
                                <option>Selesai</option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>

</div>
</body>
</html>