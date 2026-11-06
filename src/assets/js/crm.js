import { createClient } from 'https://esm.sh/@supabase/supabase-js@2';
import { SUPABASE_URL, SUPABASE_ANON_KEY } from '../../../config.js';

const ready = !SUPABASE_URL.includes('YOUR_PROJECT_ID')
  && !SUPABASE_ANON_KEY.includes('YOUR_SUPABASE_PUBLISHABLE_KEY');
const db = ready ? createClient(SUPABASE_URL, SUPABASE_ANON_KEY) : null;
const page = document.body.dataset.page;
const $ = (selector, root = document) => root.querySelector(selector);
const $$ = (selector, root = document) => [...root.querySelectorAll(selector)];
const statusLabel = { new: 'Baru', in_progress: 'Diproses', resolved: 'Selesai' };
const kindLabel = { complaint: 'Keluhan', suggestion: 'Saran' };
const categoryLabel = { service: 'Pelayanan', menu: 'Rasa menu', cleanliness: 'Kebersihan', waiting_time: 'Waktu tunggu', other: 'Lainnya' };
let session = null;
let profile = null;
let customers = [];
let complaints = [];
let members = [];
let menus = [];

function feedback(selector, text, error = false) {
  const element = $(selector);
  if (!element) return;
  element.textContent = text;
  element.classList.toggle('error', error);
}

function phoneE164(raw) {
  const digits = raw.replace(/\D/g, '');
  if (digits.startsWith('0')) return `+62${digits.slice(1)}`;
  if (digits.startsWith('62')) return `+${digits}`;
  return `+${digits}`;
}

function maskPhone(phone) {
  if (!phone || phone.length < 8) return '—';
  return `${phone.slice(0, 5)}****${phone.slice(-3)}`;
}

function updateNavigation() {
  const admin = profile?.role === 'admin';
  $('#account-name').textContent = profile?.full_name || session?.user?.email || 'Belum masuk';
  $('#logout-button').hidden = !session;
  $$('[data-admin-link]').forEach(link => { link.hidden = !admin; });
  if (page === 'home') {
    const qr = new URLSearchParams(location.search).get('source') === 'qr';
    $('#qr-notice').hidden = !qr;
    $('#consent-form').hidden = !session;
    if (session && profile) $('#consent-form').elements.whatsapp_consent.checked = profile.whatsapp_consent;
    if (qr) authMode('signup');
  }
}

async function loadProfile() {
  if (!session) {
    profile = null;
    updateNavigation();
    return;
  }
  const { data, error } = await db.from('profiles')
    .select('id, full_name, phone, whatsapp_consent, role')
    .eq('id', session.user.id)
    .single();
  if (error) {
    feedback('#auth-feedback', `Profil tidak dapat dimuat: ${error.message}`, true);
    profile = null;
  } else profile = data;
  updateNavigation();
}

function requireLogin() {
  if (!session) {
    const area = $('#auth');
    if (area) area.scrollIntoView({ behavior: 'smooth' });
    const result = $('#feedback-result');
    if (result) feedback('#feedback-result', 'Masuk atau daftar terlebih dahulu.');
    return false;
  }
  return true;
}

function requireAdmin(target) {
  if (!profile || profile.role !== 'admin') {
    feedback(target, 'Halaman ini hanya dapat diakses akun admin.', true);
    return false;
  }
  return true;
}

function authMode(mode) {
  if (!$('#login-form')) return;
  const signup = mode === 'signup';
  $('#login-form').hidden = signup;
  $('#signup-form').hidden = !signup;
  $$('.auth-tab').forEach(button => button.classList.toggle('active', button.dataset.auth === mode));
}

async function login(event) {
  event.preventDefault();
  if (!db) return feedback('#auth-feedback', 'Isi URL dan publishable key Supabase di config.js.', true);
  const form = new FormData(event.currentTarget);
  const button = $('button[type="submit"]', event.currentTarget);
  button.disabled = true;
  const { data, error } = await db.auth.signInWithPassword({
    email: String(form.get('email')).trim(),
    password: String(form.get('password')),
  });
  button.disabled = false;
  if (error) return feedback('#auth-feedback', error.message, true);
  session = data.session;
  await loadProfile();
  feedback('#auth-feedback', 'Berhasil masuk.');
}

async function signup(event) {
  event.preventDefault();
  if (!db) return feedback('#auth-feedback', 'Isi URL dan publishable key Supabase di config.js.', true);
  const form = new FormData(event.currentTarget);
  const phone = phoneE164(String(form.get('phone')).trim());
  if (!/^\+[1-9]\d{7,14}$/.test(phone)) return feedback('#auth-feedback', 'Masukkan nomor WhatsApp valid, misalnya +6281234567890.', true);
  const button = $('button[type="submit"]', event.currentTarget);
  button.disabled = true;
  const { data, error } = await db.auth.signUp({
    email: String(form.get('email')).trim(),
    password: String(form.get('password')),
    options: { data: {
      full_name: String(form.get('full_name')).trim(),
      phone,
      whatsapp_consent: form.get('whatsapp_consent') === 'on',
    } },
  });
  button.disabled = false;
  if (error) return feedback('#auth-feedback', error.message, true);
  if (data.session) {
    session = data.session;
    await loadProfile();
    feedback('#auth-feedback', 'Akun SUNA berhasil dibuat.');
  } else {
    event.currentTarget.reset();
    feedback('#auth-feedback', 'Akun terdaftar. Konfirmasi melalui email sebelum masuk.');
  }
}

async function loadCustomers() {
  if (!requireAdmin('#customers-feedback')) return;
  const body = $('#customer-rows');
  body.innerHTML = '<tr><td colspan="6" class="empty">Memuat data...</td></tr>';
  const { data, error } = await db.from('profiles')
    .select('id, full_name, email, phone, whatsapp_consent, created_at')
    .order('created_at', { ascending: false }).limit(500);
  if (error) return feedback('#customers-feedback', `Data pelanggan gagal dimuat: ${error.message}`, true);
  customers = data;
  const reg = await db.from('member_registrations').select('id, username, phone, pdp_consent, created_at')
    .order('created_at', { ascending: false }).limit(500);
  members = reg.error ? [] : reg.data;
  if (reg.error) feedback('#customers-feedback', `Member QR gagal dimuat: ${reg.error.message}`, true);
  renderCustomers();
}

function renderCustomers() {
  const term = $('#customer-search').value.trim().toLocaleLowerCase('id');
  const rows = customers.filter(customer =>
    `${customer.full_name} ${customer.email}`.toLocaleLowerCase('id').includes(term));
  const memberBody = $('#member-rows');
  const memberRows = members.filter(m => `${m.username} ${m.phone}`.toLocaleLowerCase('id').includes(term));
  if (memberBody) {
    memberBody.replaceChildren(...(memberRows.length ? memberRows.map(m => {
      const tr = document.createElement('tr');
      for (const v of [m.username, maskPhone(m.phone), m.pdp_consent ? 'Setuju' : '—', new Date(m.created_at).toLocaleDateString('id-ID')]) {
        const td = document.createElement('td'); td.textContent = v; tr.append(td);
      }
      return tr;
    }) : [Object.assign(document.createElement('tr'), { innerHTML: '<td colspan="4" class="empty">Belum ada member QR.</td>' })]));
  }
  const body = $('#customer-rows');
  if (!rows.length) {
    body.innerHTML = '<tr><td colspan="6" class="empty">Tidak ada pelanggan yang cocok.</td></tr>';
    return;
  }
  body.replaceChildren(...rows.map(customer => {
    const row = document.createElement('tr');
    const values = [
      customer.full_name,
      customer.email,
      maskPhone(customer.phone),
      customer.whatsapp_consent ? 'Setuju' : 'Belum setuju',
      new Date(customer.created_at).toLocaleDateString('id-ID'),
    ];
    for (const value of values) {
      const cell = document.createElement('td');
      cell.textContent = value || '—';
      row.append(cell);
    }
    const action = document.createElement('td');
    const edit = document.createElement('button');
    edit.type = 'button';
    edit.className = 'btn btn-outline';
    edit.textContent = 'Edit';
    edit.addEventListener('click', () => editCustomer(customer));
    action.append(edit);
    row.append(action);
    return row;
  }));
}

async function editCustomer(customer) {
  const name = prompt('Nama pelanggan:', customer.full_name);
  if (name === null) return;
  const phoneInput = prompt('Nomor WhatsApp (format +62):', customer.phone);
  if (phoneInput === null) return;
  const phone = phoneE164(phoneInput.trim());
  if (!/^\+[1-9]\d{7,14}$/.test(phone)) return feedback('#customers-feedback', 'Nomor WhatsApp tidak valid.', true);
  const consent = customer.whatsapp_consent
    ? !confirm('Pilih OK hanya jika pelanggan meminta berhenti menerima promosi WhatsApp.')
    : false;
  const { error } = await db.from('profiles').update({
    full_name: name.trim(), phone, whatsapp_consent: consent,
  }).eq('id', customer.id);
  if (error) return feedback('#customers-feedback', `Profil gagal diperbarui: ${error.message}`, true);
  feedback('#customers-feedback', 'Profil pelanggan berhasil diperbarui.');
  await loadCustomers();
}

async function updateConsent(event) {
  event.preventDefault();
  if (!requireLogin()) return;
  const checkbox = event.currentTarget.elements.whatsapp_consent;
  const { error } = await db.from('profiles')
    .update({ whatsapp_consent: checkbox.checked })
    .eq('id', session.user.id);
  if (error) return feedback('#auth-feedback', `Preferensi promo gagal disimpan: ${error.message}`, true);
  profile.whatsapp_consent = checkbox.checked;
  feedback('#auth-feedback', 'Preferensi promo berhasil disimpan.');
}

async function submitFeedback(event) {
  event.preventDefault();
  const form = new FormData(event.currentTarget);
  const rawPhone = String(form.get('sender_phone') || '').trim();
  const phone = rawPhone ? phoneE164(rawPhone) : null;
  if (phone && !/^\+[1-9]\d{7,14}$/.test(phone)) return feedback('#feedback-result', 'Nomor WhatsApp tidak valid (contoh: 081234567890).', true);
  const button = $('button[type="submit"]', event.currentTarget);
  button.disabled = true;
  const { data, error } = await db.rpc('submit_feedback', {
    p_kind: form.get('kind'),
    p_category: form.get('category'),
    p_message: String(form.get('message')).trim(),
    p_name: String(form.get('sender_name') || '').trim() || null,
    p_phone: phone,
  });
  button.disabled = false;
  if (error) return feedback('#feedback-result', `Masukan gagal dikirim: ${error.message}`, true);
  event.currentTarget.reset();
  feedback('#feedback-result', `Masukan diterima dengan nomor tiket TK-${String(data).padStart(3, '0')}. Terima kasih.`);
}

async function loadComplaints() {
  if (!requireAdmin('#complaints-feedback')) return;
  const { data, error } = await db.from('complaints')
    .select('id, ticket_no, kind, category, message, pic, status, created_at, sender_name, sender_phone, profiles(full_name, phone)')
    .order('created_at', { ascending: false }).limit(500);
  if (error) return feedback('#complaints-feedback', `Masukan gagal dimuat: ${error.message}`, true);
  complaints = data;
  $('#count-new').textContent = String(data.filter(item => item.status === 'new').length);
  $('#count-progress').textContent = String(data.filter(item => item.status === 'in_progress').length);
  $('#count-done').textContent = String(data.filter(item => item.status === 'resolved').length);
  renderComplaints();
}

function renderComplaints() {
  const filter = $('#complaint-filter').value;
  const rows = complaints.filter(item => filter === 'all' || item.status === filter);
  const body = $('#complaint-rows');
  if (!rows.length) {
    body.innerHTML = '<tr><td colspan="8" class="empty">Belum ada masukan untuk filter ini.</td></tr>';
    return;
  }
  body.replaceChildren(...rows.map(item => {
    const row = document.createElement('tr');
    const sender = item.profiles?.full_name || item.sender_name || 'Anonim';
    const senderPhone = item.profiles?.phone || item.sender_phone;
    const values = [
      `TK-${String(item.ticket_no).padStart(3, '0')}`,
      new Date(item.created_at).toLocaleString('id-ID'),
      senderPhone ? `${sender} · ${maskPhone(senderPhone)}` : sender,
      `${kindLabel[item.kind]} · ${categoryLabel[item.category]}`,
      item.message,
    ];
    for (const value of values) {
      const cell = document.createElement('td');
      cell.textContent = value;
      row.append(cell);
    }
    const picCell = document.createElement('td');
    const pic = document.createElement('select');
    for (const name of ['', 'Nopal', 'Febri', 'Rara', 'Faris', 'Dimas']) {
      const option = document.createElement('option');
      option.value = name;
      option.textContent = name || 'Pilih PIC';
      option.selected = (item.pic || '') === name;
      pic.append(option);
    }
    picCell.append(pic);
    row.append(picCell);
    const statusCell = document.createElement('td');
    const status = document.createElement('select');
    for (const [value, label] of Object.entries(statusLabel)) {
      const option = document.createElement('option');
      option.value = value;
      option.textContent = label;
      option.selected = item.status === value;
      status.append(option);
    }
    statusCell.append(status);
    row.append(statusCell);
    const actionCell = document.createElement('td');
    const save = document.createElement('button');
    save.type = 'button';
    save.className = 'btn';
    save.textContent = 'Simpan';
    save.addEventListener('click', () => updateComplaint(item.id, pic.value || null, status.value, save));
    actionCell.append(save);
    row.append(actionCell);
    return row;
  }));
}

async function updateComplaint(id, pic, status, button) {
  button.disabled = true;
  const { error } = await db.from('complaints').update({ pic, status }).eq('id', id);
  button.disabled = false;
  if (error) return feedback('#complaints-feedback', `Masukan gagal diperbarui: ${error.message}`, true);
  feedback('#complaints-feedback', 'PIC dan status berhasil diperbarui.');
  await loadComplaints();
}

async function loadMenus() {
  const admin = profile?.role === 'admin';
  if (!admin) $('#menu-form').hidden = true;
  const { data, error } = await db.from('menu_items')
    .select('id, name, description, price, is_available')
    .order('name');
  if (error) return feedback('#menu-feedback', `Katalog gagal dimuat: ${error.message}`, true);
  menus = data;
  renderMenus();
}

function renderMenus() {
  const body = $('#menu-rows');
  body.replaceChildren(...menus.map(menu => {
    const row = document.createElement('tr');
    for (const value of [menu.name, menu.description, new Intl.NumberFormat('id-ID').format(menu.price), menu.is_available ? 'Tersedia' : 'Nonaktif']) {
      const cell = document.createElement('td');
      cell.textContent = value || '—';
      row.append(cell);
    }
    const actions = document.createElement('td');
    if (profile?.role === 'admin') {
      const edit = document.createElement('button');
      edit.type = 'button';
      edit.className = 'btn btn-outline';
      edit.textContent = 'Edit';
      edit.addEventListener('click', () => {
        const form = $('#menu-form');
        form.elements.id.value = menu.id;
        form.elements.name.value = menu.name;
        form.elements.price.value = menu.price;
        form.elements.description.value = menu.description;
        form.elements.is_available.checked = menu.is_available;
        $('#cancel-menu-edit').hidden = false;
        form.scrollIntoView({ behavior: 'smooth' });
      });
      const remove = document.createElement('button');
      remove.type = 'button';
      remove.className = 'btn btn-outline';
      remove.textContent = 'Hapus';
      remove.addEventListener('click', () => deleteMenu(menu.id));
      actions.append(edit, remove);
    }
    row.append(actions);
    return row;
  }));
  if (!menus.length) body.innerHTML = '<tr><td colspan="5" class="empty">Belum ada menu.</td></tr>';
}

async function saveMenu(event) {
  event.preventDefault();
  if (!requireAdmin('#menu-feedback')) return;
  const form = new FormData(event.currentTarget);
  const id = String(form.get('id'));
  const menu = {
    name: String(form.get('name')).trim(),
    description: String(form.get('description')).trim(),
    price: Number(form.get('price')),
    is_available: form.get('is_available') === 'on',
  };
  const result = id
    ? await db.from('menu_items').update(menu).eq('id', id)
    : await db.from('menu_items').insert(menu);
  if (result.error) return feedback('#menu-feedback', `Menu gagal disimpan: ${result.error.message}`, true);
  event.currentTarget.reset();
  event.currentTarget.elements.id.value = '';
  event.currentTarget.elements.is_available.checked = true;
  $('#cancel-menu-edit').hidden = true;
  feedback('#menu-feedback', 'Menu berhasil disimpan.');
  await loadMenus();
}

async function deleteMenu(id) {
  if (!confirm('Hapus menu ini dari katalog?')) return;
  const { error } = await db.from('menu_items').delete().eq('id', id);
  if (error) return feedback('#menu-feedback', `Menu gagal dihapus: ${error.message}`, true);
  feedback('#menu-feedback', 'Menu berhasil dihapus.');
  await loadMenus();
}

async function initialize() {
  if (page === 'home') {
    $$('.auth-tab').forEach(button => button.addEventListener('click', () => authMode(button.dataset.auth)));
    $('#login-form').addEventListener('submit', login);
    $('#signup-form').addEventListener('submit', signup);
    $('#consent-form').addEventListener('submit', updateConsent);
    if (new URLSearchParams(location.search).get('source') === 'qr') {
      $('#qr-notice').hidden = false;
      authMode('signup');
    }
  }
  if (!ready) {
    $$('[id$="-feedback"], #auth-feedback').forEach(element => {
      element.textContent = 'Hubungkan project Supabase di config.js untuk mengaktifkan data.';
      element.classList.add('error');
    });
    $('#account-name').textContent = 'Supabase belum dikonfigurasi';
    return;
  }
  if (page === 'customers') {
    $('#customer-search').addEventListener('input', renderCustomers);
    $('#refresh-customers').addEventListener('click', loadCustomers);
  }
  if (page === 'feedback') $('#feedback-form').addEventListener('submit', submitFeedback);
  if (page === 'complaints') {
    $('#complaint-filter').addEventListener('change', renderComplaints);
    $('#refresh-complaints').addEventListener('click', loadComplaints);
  }
  if (page === 'catalog') {
    $('#menu-form').addEventListener('submit', saveMenu);
    $('#cancel-menu-edit').addEventListener('click', () => {
      $('#menu-form').reset();
      $('#menu-form').elements.id.value = '';
      $('#menu-form').elements.is_available.checked = true;
      $('#cancel-menu-edit').hidden = true;
    });
  }
  $('#logout-button').addEventListener('click', async () => {
    const { error } = await db.auth.signOut();
    if (error) return alert(`Gagal keluar: ${error.message}`);
    session = null;
    profile = null;
    updateNavigation();
    location.href = 'index.php';
  });
  const { data: { session: initialSession }, error } = await db.auth.getSession();
  if (error) feedback('#auth-feedback', `Sesi gagal diperiksa: ${error.message}`, true);
  session = initialSession;
  await loadProfile();
  if (page === 'feedback' && profile) $('#feedback-form').elements.sender_name.value = profile.full_name || '';
  if (page === 'customers') await loadCustomers();
  if (page === 'complaints') await loadComplaints();
  if (page === 'catalog') await loadMenus();
  db.auth.onAuthStateChange((_event, nextSession) => {
    session = nextSession;
    loadProfile();
  });
}

initialize();
