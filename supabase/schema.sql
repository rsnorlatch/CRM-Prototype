-- SUNA CRM prototype: run this once in Supabase SQL Editor.
create extension if not exists pgcrypto;

create table if not exists public.profiles (
  id uuid primary key references auth.users (id) on delete cascade,
  full_name text not null check (char_length(full_name) between 1 and 100),
  email text not null,
  phone text not null check (phone ~ '^[+][1-9][0-9]{7,14}$'),
  whatsapp_consent boolean not null default false,
  role text not null default 'customer' check (role in ('customer', 'admin')),
  created_at timestamptz not null default now()
);

alter table public.profiles add column if not exists email text;
update public.profiles p set email = u.email
from auth.users u
where u.id = p.id and p.email is null;
alter table public.profiles alter column email set not null;
alter table public.profiles drop constraint if exists profiles_phone_check;
alter table public.profiles add constraint profiles_phone_check
  check (phone ~ '^[+][1-9][0-9]{7,14}$');

create table if not exists public.menu_items (
  id uuid primary key default gen_random_uuid(),
  name text not null unique check (char_length(name) between 1 and 100),
  description text not null default '',
  price numeric(12, 2) not null check (price >= 0),
  is_available boolean not null default true,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create table if not exists public.preorders (
  id uuid primary key default gen_random_uuid(),
  customer_id uuid not null references public.profiles (id) on delete restrict,
  status text not null default 'pending'
    check (status in ('pending', 'confirmed', 'preparing', 'ready', 'completed', 'cancelled')),
  pickup_at timestamptz not null,
  notes text check (notes is null or char_length(notes) <= 500),
  total_amount numeric(12, 2) not null default 0 check (total_amount >= 0),
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create table if not exists public.order_items (
  id uuid primary key default gen_random_uuid(),
  preorder_id uuid not null references public.preorders (id) on delete cascade,
  menu_item_id uuid references public.menu_items (id) on delete set null,
  item_name text not null,
  quantity integer not null check (quantity between 1 and 20),
  unit_price numeric(12, 2) not null check (unit_price >= 0),
  line_total numeric(14, 2) generated always as (quantity * unit_price) stored
);

create table if not exists public.complaints (
  id uuid primary key default gen_random_uuid(),
  customer_id uuid not null references public.profiles (id) on delete restrict,
  kind text not null check (kind in ('complaint', 'suggestion')),
  category text not null check (category in ('service', 'menu', 'cleanliness', 'waiting_time', 'other')),
  message text not null check (char_length(message) between 1 and 500),
  pic text check (pic is null or pic in ('Nopal', 'Febri', 'Rara', 'Faris', 'Dimas')),
  status text not null default 'new' check (status in ('new', 'in_progress', 'resolved')),
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create table if not exists public.broadcast_campaigns (
  id uuid primary key default gen_random_uuid(),
  created_by uuid not null references public.profiles (id) on delete restrict,
  template_name text not null check (template_name ~ '^[A-Za-z0-9_]{1,100}$'),
  template_language text not null default 'id' check (template_language ~ '^[A-Za-z0-9_-]{1,20}$'),
  template_parameters jsonb not null default '[]'::jsonb
    check (jsonb_typeof(template_parameters) = 'array' and jsonb_array_length(template_parameters) <= 10),
  status text not null default 'queued' check (status in ('queued', 'processing', 'completed')),
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create table if not exists public.broadcast_deliveries (
  id uuid primary key default gen_random_uuid(),
  campaign_id uuid not null references public.broadcast_campaigns (id) on delete cascade,
  profile_id uuid not null references public.profiles (id) on delete restrict,
  status text not null default 'queued' check (status in ('queued', 'processing', 'sent', 'failed')),
  provider_message_id text,
  error_message text,
  attempted_at timestamptz,
  created_at timestamptz not null default now(),
  unique (campaign_id, profile_id)
);

alter table public.broadcast_deliveries drop constraint if exists broadcast_deliveries_status_check;
alter table public.broadcast_deliveries add constraint broadcast_deliveries_status_check
  check (status in ('queued', 'processing', 'sent', 'failed'));

create index if not exists preorders_customer_created_idx on public.preorders (customer_id, created_at desc);
create index if not exists preorders_status_created_idx on public.preorders (status, created_at desc);
create index if not exists order_items_preorder_idx on public.order_items (preorder_id);
create index if not exists complaints_status_created_idx on public.complaints (status, created_at desc);
create index if not exists complaints_customer_created_idx on public.complaints (customer_id, created_at desc);
create index if not exists broadcast_deliveries_queue_idx on public.broadcast_deliveries (campaign_id, status, created_at);

create or replace function public.set_updated_at()
returns trigger
language plpgsql
set search_path = ''
as $$
begin
  new.updated_at := now();
  return new;
end;
$$;

drop trigger if exists menu_items_set_updated_at on public.menu_items;
create trigger menu_items_set_updated_at before update on public.menu_items
for each row execute function public.set_updated_at();

drop trigger if exists preorders_set_updated_at on public.preorders;
create trigger preorders_set_updated_at before update on public.preorders
for each row execute function public.set_updated_at();

drop trigger if exists complaints_set_updated_at on public.complaints;
create trigger complaints_set_updated_at before update on public.complaints
for each row execute function public.set_updated_at();

drop trigger if exists broadcast_campaigns_set_updated_at on public.broadcast_campaigns;
create trigger broadcast_campaigns_set_updated_at before update on public.broadcast_campaigns
for each row execute function public.set_updated_at();

create or replace function public.handle_new_user()
returns trigger
language plpgsql
security definer
set search_path = ''
as $$
begin
  insert into public.profiles (id, full_name, email, phone, whatsapp_consent)
  values (
    new.id,
    coalesce(nullif(trim(new.raw_user_meta_data ->> 'full_name'), ''), 'Pelanggan SUNA'),
    new.email,
    nullif(trim(new.raw_user_meta_data ->> 'phone'), ''),
    coalesce((new.raw_user_meta_data ->> 'whatsapp_consent')::boolean, false)
  );
  return new;
end;
$$;

drop trigger if exists on_auth_user_created on auth.users;
create trigger on_auth_user_created after insert on auth.users
for each row execute function public.handle_new_user();

create or replace function public.is_admin()
returns boolean
language sql
stable
security definer
set search_path = ''
as $$
  select exists (
    select 1 from public.profiles p
    where p.id = (select auth.uid()) and p.role = 'admin'
  );
$$;

create or replace function public.create_preorder(
  p_items jsonb,
  p_pickup_at timestamptz,
  p_notes text default null
)
returns jsonb
language plpgsql
security definer
set search_path = ''
as $$
declare
  v_customer_id uuid := auth.uid();
  v_order_id uuid;
  v_total numeric(12, 2);
  v_item_count integer;
begin
  if jsonb_typeof(p_items) is distinct from 'array' then
    raise exception 'Pilihan menu harus berupa daftar.';
  end if;
  if v_customer_id is null then
    raise exception 'Silakan masuk terlebih dahulu.';
  end if;
  if jsonb_array_length(p_items) < 1
     or jsonb_array_length(p_items) > 20 then
    raise exception 'Pesanan harus berisi 1 sampai 20 pilihan menu.';
  end if;
  if p_pickup_at is null or p_pickup_at <= now() or p_pickup_at > now() + interval '30 days' then
    raise exception 'Waktu pengambilan harus dalam 30 hari ke depan.';
  end if;
  if p_notes is not null and char_length(p_notes) > 500 then
    raise exception 'Catatan maksimal 500 karakter.';
  end if;

  select count(*) into v_item_count
  from jsonb_to_recordset(p_items) as requested(menu_item_id uuid, quantity integer);
  if v_item_count <> jsonb_array_length(p_items)
     or exists (
       select 1
       from jsonb_to_recordset(p_items) as requested(menu_item_id uuid, quantity integer)
       where requested.menu_item_id is null
          or requested.quantity is null
          or requested.quantity < 1
          or requested.quantity > 20
     ) then
    raise exception 'Pilihan menu atau jumlah pesanan tidak valid.';
  end if;

  if (
    select count(distinct requested.menu_item_id)
    from jsonb_to_recordset(p_items) as requested(menu_item_id uuid, quantity integer)
    join public.menu_items m on m.id = requested.menu_item_id and m.is_available
  ) <> (
    select count(distinct requested.menu_item_id)
    from jsonb_to_recordset(p_items) as requested(menu_item_id uuid, quantity integer)
  ) then
    raise exception 'Salah satu menu sudah tidak tersedia.';
  end if;

  insert into public.preorders (customer_id, pickup_at, notes)
  values (v_customer_id, p_pickup_at, nullif(trim(p_notes), ''))
  returning id into v_order_id;

  insert into public.order_items (preorder_id, menu_item_id, item_name, quantity, unit_price)
  select v_order_id, m.id, m.name, sum(requested.quantity)::integer, m.price
  from jsonb_to_recordset(p_items) as requested(menu_item_id uuid, quantity integer)
  join public.menu_items m on m.id = requested.menu_item_id and m.is_available
  group by m.id, m.name, m.price;

  if exists (
    select 1 from public.order_items oi
    where oi.preorder_id = v_order_id and oi.quantity > 20
  ) then
    raise exception 'Jumlah gabungan menu yang sama maksimal 20.';
  end if;

  select coalesce(sum(oi.line_total), 0) into v_total
  from public.order_items oi where oi.preorder_id = v_order_id;
  update public.preorders set total_amount = v_total where id = v_order_id;

  return jsonb_build_object('order_id', v_order_id, 'total_amount', v_total);
end;
$$;

create or replace function public.create_broadcast_campaign(
  p_template_name text,
  p_template_language text default 'id',
  p_template_parameters jsonb default '[]'::jsonb
)
returns uuid
language plpgsql
security definer
set search_path = ''
as $$
declare
  v_campaign_id uuid;
  v_admin_id uuid := auth.uid();
begin
  if v_admin_id is null or not public.is_admin() then
    raise exception 'Hanya admin yang dapat membuat broadcast.';
  end if;
  if p_template_name !~ '^[A-Za-z0-9_]{1,100}$'
     or p_template_language !~ '^[A-Za-z0-9_-]{1,20}$'
     or jsonb_typeof(p_template_parameters) is distinct from 'array' then
    raise exception 'Template atau parameter tidak valid.';
  end if;
  if jsonb_array_length(p_template_parameters) > 10
     or exists (
       select 1
       from jsonb_array_elements(p_template_parameters) as parameter(value)
       where jsonb_typeof(parameter.value) <> 'string'
          or char_length(parameter.value #>> '{}') > 500
     ) then
    raise exception 'Template atau parameter tidak valid.';
  end if;

  insert into public.broadcast_campaigns (
    created_by, template_name, template_language, template_parameters
  )
  values (v_admin_id, p_template_name, p_template_language, p_template_parameters)
  returning id into v_campaign_id;

  insert into public.broadcast_deliveries (campaign_id, profile_id)
  select v_campaign_id, p.id
  from public.profiles p
  where p.whatsapp_consent is true
    and p.phone ~ '^[+][1-9][0-9]{7,14}$';

  if not exists (
    select 1 from public.broadcast_deliveries d where d.campaign_id = v_campaign_id
  ) then
    raise exception 'Belum ada pelanggan dengan persetujuan promo dan nomor WhatsApp valid.';
  end if;
  return v_campaign_id;
end;
$$;

create or replace function public.claim_broadcast_batch(
  p_campaign_id uuid,
  p_limit integer default 50
)
returns table (delivery_id uuid, phone text, whatsapp_consent boolean)
language plpgsql
security definer
set search_path = ''
as $$
begin
  if p_limit is null or p_limit < 1 or p_limit > 50 then
    raise exception 'Ukuran batch harus antara 1 sampai 50.';
  end if;

  return query
  with candidates as (
    select d.id
    from public.broadcast_deliveries d
    where d.campaign_id = p_campaign_id
      and (
        d.status = 'queued'
        or (d.status = 'processing' and d.attempted_at < now() - interval '15 minutes')
      )
    order by d.created_at
    for update skip locked
    limit p_limit
  ),
  claimed as (
    update public.broadcast_deliveries d
    set status = 'processing', attempted_at = now()
    from candidates c
    where d.id = c.id
    returning d.id, d.profile_id
  )
  select c.id, p.phone, p.whatsapp_consent
  from claimed c
  join public.profiles p on p.id = c.profile_id;
end;
$$;

alter table public.profiles enable row level security;
alter table public.menu_items enable row level security;
alter table public.preorders enable row level security;
alter table public.order_items enable row level security;
alter table public.broadcast_campaigns enable row level security;
alter table public.broadcast_deliveries enable row level security;

drop policy if exists "profiles_read_own_or_admin" on public.profiles;
create policy "profiles_read_own_or_admin" on public.profiles
for select to authenticated using ((select auth.uid()) = id or (select public.is_admin()));

drop policy if exists "profiles_update_own" on public.profiles;
create policy "profiles_update_own" on public.profiles
for update to authenticated using ((select auth.uid()) = id)
with check ((select auth.uid()) = id and role = 'customer');

drop policy if exists "profiles_admin_update" on public.profiles;
create policy "profiles_admin_update" on public.profiles
for update to authenticated using ((select public.is_admin()))
with check ((select public.is_admin()));

drop policy if exists "menu_read_available" on public.menu_items;
create policy "menu_read_available" on public.menu_items
for select to authenticated using (is_available or (select public.is_admin()));

drop policy if exists "menu_admin_insert" on public.menu_items;
create policy "menu_admin_insert" on public.menu_items
for insert to authenticated with check ((select public.is_admin()));

drop policy if exists "menu_admin_update" on public.menu_items;
create policy "menu_admin_update" on public.menu_items
for update to authenticated using ((select public.is_admin()))
with check ((select public.is_admin()));

drop policy if exists "menu_admin_delete" on public.menu_items;
create policy "menu_admin_delete" on public.menu_items
for delete to authenticated using ((select public.is_admin()));

drop policy if exists "preorders_read_own_or_admin" on public.preorders;
create policy "preorders_read_own_or_admin" on public.preorders
for select to authenticated using (customer_id = (select auth.uid()) or (select public.is_admin()));

drop policy if exists "preorders_admin_update" on public.preorders;
create policy "preorders_admin_update" on public.preorders
for update to authenticated using ((select public.is_admin()))
with check ((select public.is_admin()));

drop policy if exists "order_items_read_own_or_admin" on public.order_items;
create policy "order_items_read_own_or_admin" on public.order_items
for select to authenticated using (
  (select public.is_admin())
  or exists (
    select 1 from public.preorders p
    where p.id = preorder_id and p.customer_id = (select auth.uid())
  )
);

drop policy if exists "complaints_read_own_or_admin" on public.complaints;
create policy "complaints_read_own_or_admin" on public.complaints
for select to authenticated using (customer_id = (select auth.uid()) or (select public.is_admin()));

drop policy if exists "complaints_customer_insert" on public.complaints;
create policy "complaints_customer_insert" on public.complaints
for insert to authenticated with check (
  customer_id = (select auth.uid())
  and status = 'new'
  and pic is null
);

drop policy if exists "complaints_admin_update" on public.complaints;
create policy "complaints_admin_update" on public.complaints
for update to authenticated using ((select public.is_admin()))
with check ((select public.is_admin()));

drop policy if exists "campaigns_admin_read" on public.broadcast_campaigns;
create policy "campaigns_admin_read" on public.broadcast_campaigns
for select to authenticated using ((select public.is_admin()));

drop policy if exists "deliveries_admin_read" on public.broadcast_deliveries;
create policy "deliveries_admin_read" on public.broadcast_deliveries
for select to authenticated using ((select public.is_admin()));

revoke all on public.profiles, public.menu_items, public.preorders, public.order_items,
  public.complaints, public.broadcast_campaigns, public.broadcast_deliveries from anon, authenticated;
grant select on public.profiles, public.menu_items, public.preorders, public.order_items,
  public.complaints, public.broadcast_campaigns, public.broadcast_deliveries to authenticated;
grant update (full_name, phone, whatsapp_consent) on public.profiles to authenticated;
grant insert, update, delete on public.menu_items to authenticated;
grant update (status) on public.preorders to authenticated;
grant insert, update (pic, status) on public.complaints to authenticated;
grant usage, select on all sequences in schema public to authenticated;

revoke all on function public.set_updated_at() from public, anon, authenticated;
revoke all on function public.handle_new_user() from public, anon, authenticated;
revoke all on function public.is_admin() from public, anon, authenticated;
revoke all on function public.create_preorder(jsonb, timestamptz, text) from public, anon, authenticated;
revoke all on function public.create_broadcast_campaign(text, text, jsonb) from public, anon, authenticated;
revoke all on function public.claim_broadcast_batch(uuid, integer) from public, anon, authenticated;
grant execute on function public.is_admin() to authenticated;
grant execute on function public.create_preorder(jsonb, timestamptz, text) to authenticated;
grant execute on function public.create_broadcast_campaign(text, text, jsonb) to authenticated;
grant execute on function public.claim_broadcast_batch(uuid, integer) to service_role;

-- Optional demo menu; change or delete these examples to match SUNA's real menu.
insert into public.menu_items (name, description, price) values
  ('Kopi Susu SUNA', 'Kopi susu gula aren — menu demo, silakan sesuaikan.', 22000),
  ('Matcha Latte', 'Matcha creamy — menu demo, silakan sesuaikan.', 25000),
  ('Roti Panggang', 'Roti panggang — menu demo, silakan sesuaikan.', 18000)
on conflict (name) do nothing;

-- After registering an account, promote the chosen account to admin in SQL Editor:
-- update public.profiles set role = 'admin' where id = 'AUTH_USER_UUID';

-- =====================================================================
-- MERGE: fitur keluhan dari prototipe PHP/MySQL (nomor tiket TK-xxx, pengirim anonim + WA opsional)
-- =====================================================================
alter table public.complaints add column if not exists ticket_no bigint generated always as identity;
alter table public.complaints add column if not exists sender_name text;
alter table public.complaints add column if not exists sender_phone text;
alter table public.complaints drop constraint if exists complaints_sender_name_check;
alter table public.complaints add constraint complaints_sender_name_check check (sender_name is null or char_length(sender_name) <= 60);
alter table public.complaints drop constraint if exists complaints_sender_phone_check;
alter table public.complaints add constraint complaints_sender_phone_check check (sender_phone is null or sender_phone ~ '^[+][1-9][0-9]{7,14}$');
alter table public.complaints alter column customer_id drop not null;

create or replace function public.submit_feedback(
  p_kind text, p_category text, p_message text, p_name text default null, p_phone text default null)
returns bigint language plpgsql security definer set search_path = '' as $$
declare v_uid uuid := auth.uid(); v_name text := nullif(trim(p_name), ''); v_phone text := nullif(trim(p_phone), ''); v_no bigint;
begin
  if v_uid is not null then
    select coalesce(v_name, p.full_name), coalesce(v_phone, p.phone) into v_name, v_phone from public.profiles p where p.id = v_uid;
  end if;
  insert into public.complaints (customer_id, kind, category, message, sender_name, sender_phone)
  values (v_uid, p_kind, p_category, trim(p_message), v_name, v_phone) returning ticket_no into v_no;
  return v_no;
end $$;
revoke all on function public.submit_feedback(text, text, text, text, text) from public;
grant execute on function public.submit_feedback(text, text, text, text, text) to anon, authenticated;

-- =====================================================================
-- MERGE v2: pendaftaran member cepat via QR (dari pendaftaran_pelanggan.php) — tanpa email/password, wajib persetujuan PDP
-- =====================================================================
create table if not exists public.member_registrations (
  id uuid primary key default gen_random_uuid(),
  username text not null check (char_length(username) between 3 and 50),
  phone text not null unique check (phone ~ '^[+][1-9][0-9]{7,14}$'),
  pdp_consent boolean not null check (pdp_consent = true),
  source text not null default 'qr',
  created_at timestamptz not null default now()
);
alter table public.member_registrations enable row level security;
drop policy if exists "registrations_admin_read" on public.member_registrations;
create policy "registrations_admin_read" on public.member_registrations
  for select to authenticated using ((select public.is_admin()));
revoke all on public.member_registrations from anon, authenticated;
grant select on public.member_registrations to authenticated;

create or replace function public.register_member(p_username text, p_phone text, p_consent boolean)
returns void language plpgsql security definer set search_path = '' as $$
begin
  insert into public.member_registrations (username, phone, pdp_consent)
  values (trim(p_username), trim(p_phone), p_consent);
end $$;
revoke all on function public.register_member(text, text, boolean) from public;
grant execute on function public.register_member(text, text, boolean) to anon, authenticated;
