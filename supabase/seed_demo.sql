-- DATA SAMARAN (asumsi) untuk worksheet 11.4 — jalankan SETELAH schema.sql. 20 pelanggan + 6 tiket.
-- Akun demo (demo1..20@suna.test) memakai password acak, hanya untuk mengisi tabel Data Pelanggan.
insert into auth.users (instance_id,id,aud,role,email,encrypted_password,email_confirmed_at,raw_app_meta_data,raw_user_meta_data,created_at,updated_at,confirmation_token,recovery_token,email_change_token_new,email_change)
select '00000000-0000-0000-0000-000000000000', gen_random_uuid(), 'authenticated','authenticated',
  'demo'||i||'@suna.test', crypt(gen_random_uuid()::text, gen_salt('bf')), now(),
  '{"provider":"email","providers":["email"]}'::jsonb,
  jsonb_build_object('full_name',(array['Raka','Nadia','Bima','Sekar','Fajar','Laras','Dimas','Ayu','Citra','Satria','Rafi','Dewi','Galih','Intan','Joko','Kirana','Lintang','Mawar','Naufal','Putri'])[i],
    'phone','+62812'||lpad((10000000+i*377311)::text,8,'0'),'whatsapp_consent',(i%3<>0)),
  now() - (i||' days')::interval, now(), '','','',''
from generate_series(1,20) i
on conflict do nothing;

insert into public.complaints (kind,category,message,pic,status,sender_name,created_at) values
('complaint','service','Kasir kurang ramah saat jam ramai',null,'new','Raka',now()-interval '30 min'),
('complaint','menu','Rasa taro kurang manis dibanding biasanya','Nopal','in_progress','Nadia',now()-interval '3 hours'),
('complaint','waiting_time','Antre lebih dari 20 menit','Febri','in_progress','Bima',now()-interval '1 day'),
('suggestion','menu','Tambah varian rasa baru dan promo musiman','Rara','resolved','Sekar',now()-interval '2 days'),
('complaint','cleanliness','Meja outdoor kurang bersih','Faris','resolved','Fajar',now()-interval '3 days'),
('complaint','service','Nota wajib pakai nomor HP, khawatir privasi','Dimas','new','Laras',now()-interval '20 min');

-- 5 member pendaftaran cepat QR (samaran)
insert into public.member_registrations (username, phone, pdp_consent, created_at)
select 'member_'||i, '+62857'||lpad((20000000+i*531977)::text,8,'0'), true, now()-(i||' hours')::interval
from generate_series(1,5) i on conflict do nothing;
