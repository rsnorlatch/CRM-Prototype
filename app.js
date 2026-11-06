import { createClient } from 'https://esm.sh/@supabase/supabase-js@2';
import { SUPABASE_URL, SUPABASE_ANON_KEY } from './config.js';

const clientConfigured = !SUPABASE_URL.includes('YOUR_PROJECT_ID')
  && !SUPABASE_ANON_KEY.includes('YOUR_SUPABASE_PUBLISHABLE_KEY');
const supabase = clientConfigured ? createClient(SUPABASE_URL, SUPABASE_ANON_KEY) : null;
const views = ['home', 'preorder', 'orders', 'broadcast'];
const state = { user: null, profile: null, menu: [], cart: new Map(), toastTimer: null };
const currency = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 });
const statusNames = {
  pending: 'Menunggu konfirmasi',
  confirmed: 'Dikonfirmasi',
  preparing: 'Sedang disiapkan',
  ready: 'Siap diambil',
  completed: 'Selesai',
  cancelled: 'Dibatalkan',
};

const $ = (selector, root = document) => root.querySelector(selector);
const $$ = (selector, root = document) => [...root.querySelectorAll(selector)];

function message(target, text, isError = false) {
  const element = typeof target === 'string' ? $(target) : target;
  element.textContent = text;
  element.classList.toggle('error', isError);
}

function showToast(text, isError = false) {
  const toast = $('#toast');
  toast.textContent = text;
  toast.classList.toggle('error', isError);
  toast.classList.add('show');
  clearTimeout(state.toastTimer);
  state.toastTimer = setTimeout(() => toast.classList.remove('show'), 4200);
}

function setView(view) {
  if (!views.includes(view)) return;
  if (view === 'orders' && state.profile?.role !== 'admin') view = 'home';
  for (const name of views) $(`#${name}-view`).hidden = name !== view;
  $$('.nav-link').forEach(button => button.classList.toggle('active', button.dataset.view === view));
  updateRoleNavigation();
  if (view === 'preorder') loadMenu();
  if (view === 'orders') loadOrders();
  if (view === 'broadcast') loadCampaigns();
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function setAuthMode(mode) {
  const signup = mode === 'signup';
  $('#login-form').hidden = signup;
  $('#signup-form').hidden = !signup;
  $$('.auth-tab').forEach(tab => {
    const active = tab.dataset.auth === mode;
    tab.classList.toggle('active', active);
    tab.setAttribute('aria-selected', String(active));
  });
  message('#auth-feedback', '');
}

function configureNavigation() {
  $$('.nav-link').forEach(button => button.addEventListener('click', () => setView(button.dataset.view)));
  $('#login-shortcut').addEventListener('click', () => {
    setView('home');
    $('#auth-section').scrollIntoView({ behavior: 'smooth' });
  });
  $('#hero-login-button').addEventListener('click', () => {
    $('#auth-section').scrollIntoView({ behavior: 'smooth' });
    setAuthMode('signup');
  });
  $('#hero-order-button').addEventListener('click', () => {
    setView('preorder');
  });
  $('#preorder-login-button').addEventListener('click', () => {
    setView('home');
    $('#auth-section').scrollIntoView({ behavior: 'smooth' });
  });
  $$('.auth-tab').forEach(tab => tab.addEventListener('click', () => setAuthMode(tab.dataset.auth)));
  $('#reload-menu').addEventListener('click', loadMenu);
  $('#reload-campaigns').addEventListener('click', loadCampaigns);
}

function normalizePhone(raw) {
  const digits = raw.replace(/\D/g, '');
  if (digits.startsWith('0')) return `+62${digits.slice(1)}`;
  if (digits.startsWith('62')) return `+${digits}`;
  return `+${digits}`;
}

function maskPhone(phone) {
  if (!phone || phone.length < 8) return '—';
  return `${phone.slice(0, 5)}****${phone.slice(-3)}`;
}

function setSession(session) {
  state.user = session?.user ?? null;
  updateAccount();
  if (state.user) loadProfile();
  else {
    state.profile = null;
    updateRoleNavigation();
  }
}

function updateAccount() {
  const label = $('#account-label');
  const login = $('#login-shortcut');
  const logout = $('#logout-button');
  label.textContent = state.profile?.full_name || state.user?.email || 'Belum masuk';
  login.hidden = Boolean(state.user);
  logout.hidden = !state.user;
}

function updateRoleNavigation() {
  const admin = state.profile?.role === 'admin';
  const loggedIn = Boolean(state.user);
  $('[data-view="preorder"]').hidden = false;
  $('[data-view="orders"]').hidden = !admin;
  $('[data-view="broadcast"]').hidden = false;
  $('#preorder-form').hidden = !loggedIn || !supabase;
  $('#preorder-access-notice').hidden = loggedIn && Boolean(supabase);
  $('#broadcast-form').hidden = !admin || !supabase;
  $('#broadcast-access-notice').hidden = admin && Boolean(supabase);
  if (!admin) {
    $('#broadcast-access-notice').textContent = loggedIn
      ? 'Akun ini belum memiliki akses admin untuk membuat atau melihat kampanye.'
      : 'Masuk menggunakan akun admin untuk membuat dan melihat kampanye.';
  }
  if (!supabase) {
    $('#broadcast-access-notice').textContent = 'Hubungkan project Supabase di config.js dan masuk sebagai admin untuk mengaktifkan broadcast.';
  }
  $('#account-label').textContent = state.profile?.full_name || state.user?.email || 'Belum masuk';
}

async function loadProfile() {
  if (!supabase || !state.user) return;
  const { data, error } = await supabase.from('profiles')
    .select('id, full_name, phone, whatsapp_consent, role')
    .eq('id', state.user.id)
    .single();
  if (error) {
    message('#auth-feedback', `Profil belum dapat dimuat: ${error.message}`, true);
    return;
  }
  state.profile = data;
  updateRoleNavigation();
}

async function submitLogin(event) {
  event.preventDefault();
  if (!supabase) return showToast('Isi URL dan publishable key Supabase di config.js terlebih dahulu.', true);
  const form = new FormData(event.currentTarget);
  const button = $('button[type="submit"]', event.currentTarget);
  button.disabled = true;
  message('#auth-feedback', 'Sedang masuk...');
  const { data, error } = await supabase.auth.signInWithPassword({
    email: String(form.get('email')).trim(),
    password: String(form.get('password')),
  });
  button.disabled = false;
  if (error) return message('#auth-feedback', error.message, true);
  setSession(data.session);
  message('#auth-feedback', 'Berhasil masuk. Silakan lanjutkan di menu Pre-order.');
}

async function submitSignup(event) {
  event.preventDefault();
  if (!supabase) return showToast('Isi URL dan publishable key Supabase di config.js terlebih dahulu.', true);
  const form = new FormData(event.currentTarget);
  const phone = normalizePhone(String(form.get('phone')).trim());
  if (!/^\+[1-9]\d{7,14}$/.test(phone)) {
    return message('#auth-feedback', 'Masukkan nomor WhatsApp yang valid, misalnya +6281234567890.', true);
  }
  const button = $('button[type="submit"]', event.currentTarget);
  button.disabled = true;
  message('#auth-feedback', 'Sedang membuat akun...');
  const { data, error } = await supabase.auth.signUp({
    email: String(form.get('email')).trim(),
    password: String(form.get('password')),
    options: {
      data: {
        full_name: String(form.get('full_name')).trim(),
        phone,
        whatsapp_consent: form.get('whatsapp_consent') === 'on',
      },
    },
  });
  button.disabled = false;
  if (error) return message('#auth-feedback', error.message, true);
  if (data.session) {
    setSession(data.session);
    message('#auth-feedback', 'Akun berhasil dibuat. Kamu sekarang bisa membuat pre-order.');
  } else {
    message('#auth-feedback', 'Akun terdaftar. Periksa email untuk konfirmasi, lalu masuk.');
    event.currentTarget.reset();
  }
}

function renderMenu() {
  const list = $('#menu-list');
  if (!state.menu.length) {
    list.innerHTML = '<p class="muted">Belum ada menu yang tersedia. Admin dapat mengisi menu lewat tabel menu_items di Supabase.</p>';
    return;
  }
  list.replaceChildren(...state.menu.map(item => {
    const row = document.createElement('article');
    row.className = 'menu-card';
    const details = document.createElement('div');
    const title = document.createElement('h3');
    title.textContent = item.name;
    const description = document.createElement('p');
    description.textContent = item.description || 'Menu pilihan SUNA.';
    const price = document.createElement('div');
    price.className = 'menu-price';
    price.textContent = currency.format(item.price);
    details.append(title, description, price);
    const controls = document.createElement('div');
    controls.className = 'menu-controls';
    const count = document.createElement('span');
    count.className = 'qty-count';
    count.textContent = String(state.cart.get(item.id)?.quantity ?? 0);
    const minus = document.createElement('button');
    minus.type = 'button';
    minus.className = 'qty-button';
    minus.setAttribute('aria-label', `Kurangi ${item.name}`);
    minus.textContent = '−';
    minus.addEventListener('click', () => changeCart(item.id, -1));
    const plus = document.createElement('button');
    plus.type = 'button';
    plus.className = 'qty-button';
    plus.setAttribute('aria-label', `Tambah ${item.name}`);
    plus.textContent = '+';
    plus.addEventListener('click', () => changeCart(item.id, 1));
    controls.append(minus, count, plus);
    row.append(details, controls);
    return row;
  }));
  renderCart();
}

function changeCart(itemId, delta) {
  const item = state.menu.find(entry => entry.id === itemId);
  if (!item) return;
  const quantity = (state.cart.get(itemId)?.quantity ?? 0) + delta;
  if (quantity <= 0) state.cart.delete(itemId);
  else if (quantity <= 20) state.cart.set(itemId, { item, quantity });
  renderMenu();
}

function renderCart() {
  const summary = $('#cart-summary');
  if (!state.cart.size) {
    summary.innerHTML = '<p class="muted">Belum ada menu dipilih.</p>';
    return;
  }
  const rows = [...state.cart.values()];
  summary.replaceChildren(...rows.map(({ item, quantity }) => {
    const row = document.createElement('div');
    row.className = 'cart-row';
    const name = document.createElement('span');
    name.textContent = `${item.name} × ${quantity}`;
    const price = document.createElement('strong');
    price.textContent = currency.format(item.price * quantity);
    row.append(name, price);
    return row;
  }));
  const total = rows.reduce((sum, { item, quantity }) => sum + item.price * quantity, 0);
  const totalRow = document.createElement('div');
  totalRow.className = 'order-total';
  totalRow.innerHTML = `<span>Perkiraan total</span><span>${currency.format(total)}</span>`;
  summary.append(totalRow);
}

async function loadMenu() {
  if (!supabase) return;
  $('#menu-list').innerHTML = '<p class="muted">Memuat menu...</p>';
  const { data, error } = await supabase.from('menu_items')
    .select('id, name, description, price')
    .eq('is_available', true)
    .order('name');
  if (error) {
    $('#menu-list').textContent = `Menu tidak dapat dimuat: ${error.message}`;
    $('#menu-list').className = 'menu-list form-feedback error';
    return;
  }
  state.menu = data;
  renderMenu();
}

async function submitPreorder(event) {
  event.preventDefault();
  if (!supabase || !state.user) return showToast('Masuk terlebih dahulu untuk membuat pre-order.', true);
  if (!state.cart.size) return message('#preorder-feedback', 'Pilih setidaknya satu menu.', true);
  const form = new FormData(event.currentTarget);
  const pickupAt = new Date(String(form.get('pickup_at')));
  if (Number.isNaN(pickupAt.getTime()) || pickupAt <= new Date()) {
    return message('#preorder-feedback', 'Pilih waktu pengambilan di masa mendatang.', true);
  }
  const items = [...state.cart.values()].map(({ item, quantity }) => ({ menu_item_id: item.id, quantity }));
  const submit = $('button[type="submit"]', event.currentTarget);
  submit.disabled = true;
  message('#preorder-feedback', 'Menyimpan pre-order...');
  const { data, error } = await supabase.rpc('create_preorder', {
    p_items: items,
    p_pickup_at: pickupAt.toISOString(),
    p_notes: String(form.get('notes')).trim() || null,
  });
  submit.disabled = false;
  if (error) return message('#preorder-feedback', error.message, true);
  state.cart.clear();
  event.currentTarget.reset();
  renderMenu();
  message('#preorder-feedback', `Pre-order berhasil dibuat. Nomor pesanan: ${data.order_id}. Total ${currency.format(data.total_amount)}.`);
  showToast('Pre-order berhasil disimpan.');
}

async function loadOrders() {
  if (!supabase || state.profile?.role !== 'admin') return;
  const list = $('#orders-list');
  list.innerHTML = '<p class="muted">Memuat pesanan...</p>';
  const { data, error } = await supabase.from('preorders')
    .select('id, status, pickup_at, notes, total_amount, created_at, profiles(full_name, phone), order_items(item_name, quantity, unit_price)')
    .order('created_at', { ascending: false })
    .limit(100);
  if (error) {
    list.textContent = `Pesanan tidak dapat dimuat: ${error.message}`;
    return;
  }
  if (!data.length) {
    list.innerHTML = '<p class="muted">Belum ada pre-order.</p>';
    return;
  }
  list.replaceChildren(...data.map(order => {
    const article = document.createElement('article');
    article.className = 'order-card';
    const head = document.createElement('div');
    head.className = 'order-card-head';
    const detail = document.createElement('div');
    const name = document.createElement('h3');
    name.textContent = order.profiles?.full_name || 'Pelanggan';
    const meta = document.createElement('p');
    meta.textContent = `${maskPhone(order.profiles?.phone)} · ${new Date(order.pickup_at).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' })}`;
    const items = document.createElement('p');
    items.className = 'order-items';
    items.textContent = `${order.order_items.map(item => `${item.item_name} × ${item.quantity}`).join(', ')} · ${currency.format(order.total_amount)}${order.notes ? ` · Catatan: ${order.notes}` : ''}`;
    const badge = document.createElement('span');
    badge.className = `status-pill${order.status === 'cancelled' ? ' cancelled' : ''}`;
    badge.textContent = statusNames[order.status] || order.status;
    detail.append(name, meta);
    head.append(detail, badge);
    article.append(head, items);
    const controls = document.createElement('div');
    controls.className = 'order-admin-controls';
    const selectLabel = document.createElement('label');
    selectLabel.textContent = 'Ubah status';
    const select = document.createElement('select');
    select.setAttribute('aria-label', `Status pesanan ${order.id}`);
    for (const [value, label] of Object.entries(statusNames)) {
      const option = document.createElement('option');
      option.value = value;
      option.textContent = label;
      option.selected = value === order.status;
      select.append(option);
    }
    selectLabel.append(select);
    const templateLabel = document.createElement('label');
    templateLabel.textContent = 'Template WA (opsional)';
    const template = document.createElement('input');
    template.type = 'text';
    template.placeholder = 'suna_order_update';
    template.pattern = '[a-zA-Z0-9_]+';
    templateLabel.append(template);
    const save = document.createElement('button');
    save.type = 'button';
    save.className = 'button button-dark';
    save.textContent = 'Simpan status';
    save.addEventListener('click', () => updateOrder(order, select.value, template.value.trim(), save));
    controls.append(selectLabel, templateLabel, save);
    article.append(controls);
    return article;
  }));
}

async function updateOrder(order, status, templateName, button) {
  button.disabled = true;
  const { error } = await supabase.from('preorders').update({ status }).eq('id', order.id);
  button.disabled = false;
  if (error) return message('#orders-feedback', `Status gagal disimpan: ${error.message}`, true);
  message('#orders-feedback', `Status pesanan ${order.id} tersimpan.`);
  if (templateName) {
    const { error: sendError } = await supabase.functions.invoke('whatsapp', {
      body: { action: 'order_status', order_id: order.id, template_name: templateName, template_language: 'id' },
    });
    if (sendError) message('#orders-feedback', `Status tersimpan, tetapi notifikasi WhatsApp gagal: ${sendError.message}`, true);
    else message('#orders-feedback', 'Status tersimpan dan notifikasi WhatsApp berhasil dikirim.');
  }
  loadOrders();
}

async function submitBroadcast(event) {
  event.preventDefault();
  if (!supabase || state.profile?.role !== 'admin') return showToast('Fitur ini hanya tersedia untuk admin.', true);
  const form = new FormData(event.currentTarget);
  const templateName = String(form.get('template_name')).trim();
  const language = String(form.get('template_language')).trim();
  const parameters = String(form.get('template_parameters')).trim()
    ? String(form.get('template_parameters')).split(',').map(value => value.trim())
    : [];
  const button = $('button[type="submit"]', event.currentTarget);
  button.disabled = true;
  message('#broadcast-feedback', 'Membuat kampanye...');
  const { data: campaignId, error } = await supabase.rpc('create_broadcast_campaign', {
    p_template_name: templateName,
    p_template_language: language,
    p_template_parameters: parameters,
  });
  if (error) {
    button.disabled = false;
    return message('#broadcast-feedback', `Kampanye gagal dibuat: ${error.message}`, true);
  }
  await sendCampaignBatch(campaignId, button);
  event.currentTarget.reset();
  $('input[name="template_language"]', event.currentTarget).value = 'id';
  await loadCampaigns();
}

async function sendCampaignBatch(campaignId, button) {
  button.disabled = true;
  message('#broadcast-feedback', 'Mengirim batch WhatsApp...');
  const { data, error } = await supabase.functions.invoke('whatsapp', {
    body: { action: 'broadcast', campaign_id: campaignId },
  });
  button.disabled = false;
  if (error) {
    message('#broadcast-feedback', `Kampanye tercatat, tetapi batch gagal dikirim: ${error.message}. Kamu dapat mencoba lagi dari riwayat.`, true);
    return;
  }
  const summary = `Batch selesai: ${data.sent} terkirim, ${data.failed} gagal. ${data.remaining} penerima masih menunggu.`;
  message('#broadcast-feedback', summary);
  if (data.remaining > 0) addNextBatchButton(campaignId);
}

function addNextBatchButton(campaignId) {
  const feedback = $('#broadcast-feedback');
  const button = document.createElement('button');
  button.type = 'button';
  button.className = 'button button-outline button-small';
  button.textContent = 'Kirim batch berikutnya';
  button.addEventListener('click', () => {
    button.remove();
    sendCampaignBatch(campaignId, button);
  });
  feedback.append(' ', button);
}

async function loadCampaigns() {
  if (!supabase || state.profile?.role !== 'admin') return;
  const list = $('#campaign-list');
  const { data, error } = await supabase.from('broadcast_campaigns')
    .select('id, template_name, template_language, status, created_at')
    .order('created_at', { ascending: false })
    .limit(10);
  if (error) {
    list.textContent = `Riwayat kampanye gagal dimuat: ${error.message}`;
    return;
  }
  if (!data.length) {
    list.innerHTML = '<p class="muted">Belum ada kampanye.</p>';
    return;
  }
  list.replaceChildren(...data.map(campaign => {
    const row = document.createElement('div');
    row.className = 'campaign-row';
    const title = document.createElement('strong');
    title.textContent = campaign.template_name;
    const detail = document.createElement('p');
    detail.textContent = `${campaign.status} · ${campaign.template_language} · ${new Date(campaign.created_at).toLocaleString('id-ID')}`;
    row.append(title, detail);
    if (campaign.status !== 'completed') {
      const resume = document.createElement('button');
      resume.type = 'button';
      resume.className = 'button button-outline';
      resume.textContent = 'Lanjutkan batch';
      resume.addEventListener('click', () => sendCampaignBatch(campaign.id, resume));
      row.append(resume);
    }
    return row;
  }));
}

function attachForms() {
  $('#login-form').addEventListener('submit', submitLogin);
  $('#signup-form').addEventListener('submit', submitSignup);
  $('#preorder-form').addEventListener('submit', submitPreorder);
  $('#broadcast-form').addEventListener('submit', submitBroadcast);
  $('#logout-button').addEventListener('click', async () => {
    const { error } = await supabase.auth.signOut();
    if (error) return showToast(`Gagal keluar: ${error.message}`, true);
    setSession(null);
    showToast('Kamu sudah keluar.');
    setView('home');
  });
}

async function initialize() {
  configureNavigation();
  if (!clientConfigured) {
    $('#menu-list').innerHTML = '<p class="muted">Hubungkan Supabase dengan mengisi config.js untuk mengaktifkan fitur.</p>';
    attachForms();
    updateRoleNavigation();
    const requestedView = window.location.hash.slice(1);
    const queryView = new URLSearchParams(window.location.search).get('view');
    const initialView = views.includes(queryView) ? queryView : requestedView;
    if (views.includes(initialView)) setView(initialView);
    return;
  }
  attachForms();
  const { data: { session }, error } = await supabase.auth.getSession();
  if (error) showToast(`Sesi gagal diperiksa: ${error.message}`, true);
  setSession(session);
  if (session) await loadProfile();
  supabase.auth.onAuthStateChange((_event, nextSession) => {
    setSession(nextSession);
  });
  const queryView = new URLSearchParams(window.location.search).get('view');
  const requestedView = views.includes(queryView) ? queryView : window.location.hash.slice(1);
  if (views.includes(requestedView)) setView(requestedView);
}

initialize();
