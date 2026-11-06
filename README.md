# SUNA CRM Prototype

Prototipe CRM untuk worksheet P11 SUNA. Aplikasi menggunakan halaman PHP yang sudah ada pada project (`src/`) dan Supabase untuk login serta penyimpanan data. Tampilan mengikuti wireframe GitHub: sidebar biru, panel konten putih, tabel abu-abu, dan logo SUNA.

## Fitur yang disesuaikan

- **Pendaftaran member / QR:** buka `src/index.php?source=qr` dan gunakan URL tersebut sebagai tujuan QR. Pelanggan dapat masuk atau mendaftar; persetujuan WhatsApp tidak wajib dan dapat diubah dari halaman akun.
- **Data pelanggan:** pencarian dan perubahan nama/nomor. Nomor WhatsApp disamarkan di daftar.
- **Katalog menu:** admin dapat melihat, menambah, mengubah, dan menghapus menu.
- **Keluhan & saran:** pelanggan mengirim masukan, admin menunjuk PIC (termasuk Nopal/Febri) serta memperbarui status.
- **Pre-order dan status pesanan:** gunakan `index.html#preorder` dan `index.html#orders`.
- **Broadcast promosi:** tersedia di `index.html#broadcast`; pesan hanya dikirim kepada pelanggan yang menyetujui promo.

## Menyiapkan Supabase

1. Buat project Supabase dan jalankan seluruh `supabase/schema.sql` di **SQL Editor**. SQL menyiapkan tabel pelanggan, katalog, pre-order, keluhan, kampanye, kebijakan RLS, dan beberapa menu simulasi.
2. Salin **Project URL** dan **publishable key** ke `config.js`. Jangan pernah menaruh service-role key atau token WhatsApp di file web.
3. Daftarkan akun admin melalui halaman pendaftaran. Cari UUID akun tersebut di **Authentication → Users**, lalu jadikan admin melalui SQL Editor:

   ```sql
   update public.profiles
   set role = 'admin'
   where id = 'AUTH_USER_UUID';
   ```

4. Di pengaturan Auth Supabase, gunakan konfirmasi email untuk akun jika diinginkan. Jika konfirmasi email aktif, buka tautan email sebelum masuk.
5. Untuk perubahan SQL yang dijalankan sebelumnya, jalankan kembali `supabase/schema.sql`; perubahan kolom dan kebijakan baru memakai `IF NOT EXISTS` dan penyesuaian constraint yang dapat diulang. Perubahan data lama yang gagal memenuhi format nomor WhatsApp perlu diperbaiki terlebih dahulu.

## Menjalankan website

Jalankan server PHP dari folder project:

```sh
php -S localhost:8000 -t .
```

Buka `http://localhost:8000/` untuk beranda PHP atau `http://localhost:8000/index.html` untuk prototipe pre-order/broadcast. Browser perlu akses internet untuk mengunduh Supabase JS.

## WhatsApp

Pengiriman nyata membutuhkan WhatsApp Business Platform, Edge Function `whatsapp`, secrets `WHATSAPP_ACCESS_TOKEN`, `WHATSAPP_PHONE_NUMBER_ID`, dan `WHATSAPP_API_VERSION`, serta template WhatsApp yang telah disetujui. Ikuti petunjuk deployment di `supabase/functions/whatsapp/` dan gunakan template status pesanan yang menerima nama pelanggan serta status.

Kampanye dijalankan dalam batch 50 penerima; lanjutkan batch melalui riwayat kampanye sampai selesai. Jangan kirim promosi tanpa persetujuan yang dapat dibuktikan. Gunakan data simulasi untuk tugas, demo, dan tangkapan layar; jangan tampilkan nomor WhatsApp pelanggan nyata.

Menu awal, identitas, harga, dan persetujuan pada prototipe hanya data uji. Sesuaikan menu simulasi dengan menu SUNA sebelum demo.
