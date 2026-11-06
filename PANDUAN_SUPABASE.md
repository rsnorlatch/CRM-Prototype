# Panduan Connect Supabase — SUNA CRM (gabungan)

1. **Buat project**: supabase.com → New project (catat password DB). Tunggu status *Healthy*.
2. **Jalankan SQL** (menu *SQL Editor* → New query), berurutan:
   a. tempel seluruh `supabase/schema.sql` → Run (sudah termasuk tiket TK-xxx & form keluhan tanpa login)
   b. tempel `supabase/seed_demo.sql` → Run (20 pelanggan + 6 tiket samaran, label "asumsi")
3. **Ambil kunci**: Project Settings → API (atau tombol *Connect*) → salin **Project URL** dan **anon / publishable key**.
   Buka `config.js`, ganti `YOUR_PROJECT_ID` dan `YOUR_SUPABASE_PUBLISHABLE_KEY`. JANGAN pakai service_role key.
4. **Auth**: Authentication → Providers → Email aktif. Untuk demo, matikan *Confirm email* agar daftar langsung bisa masuk.
5. **Buat admin**: buka website → *Daftar member* → buat akun Anda. Lalu Authentication → Users → salin UUID, jalankan:
   `update public.profiles set role='admin' where id='UUID_ANDA';` → logout & login ulang.
6. **Pendaftaran QR**: arahkan QR ke `src/pendaftaran_pelanggan.php` (member cepat tanpa password, wajib persetujuan PDP).
7. **Jalankan web** (butuh PHP & internet): `php -S localhost:8000 -t .` → buka http://localhost:8000
7. **Uji & ambil bukti** (nomor WA di tangkapan layar harus tersamarkan):
   - Gambar 11.2: `Data Pelanggan` (20 data, coba cari & ubah nama)
   - Gambar 11.3: `Pre-Order` (index.html#preorder) lalu `Pesanan` (index.html#orders) ubah status
   - Gambar 11.4: `Keluhan & Saran` — kirim 1 masukan dari `Kirim Masukan`, lalu di admin pilih PIC & ubah status
   - Gambar 11.5: `Katalog Menu` (tambah/ubah menu)

Masalah umum: layar tulisan "Hubungkan project Supabase" → config.js belum diisi · "permission denied" → akun belum jadi admin ·
"new row violates check" → nomor WA harus format 08xx/62xx · halaman kosong → buka lewat server PHP, bukan klik dua kali file.
