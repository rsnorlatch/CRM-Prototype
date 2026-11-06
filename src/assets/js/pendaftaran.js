import { createClient } from 'https://esm.sh/@supabase/supabase-js@2';
import { SUPABASE_URL, SUPABASE_ANON_KEY } from '../../../config.js';

const ready = !SUPABASE_URL.includes('YOUR_PROJECT_ID') && !SUPABASE_ANON_KEY.includes('YOUR_SUPABASE_PUBLISHABLE_KEY');
const db = ready ? createClient(SUPABASE_URL, SUPABASE_ANON_KEY) : null;
const $ = s => document.querySelector(s);

function showError(text) { const e = $('#reg-error'); e.textContent = text; e.hidden = !text; }
function toE164(raw) {
  const d = raw.replace(/[\s\-().]/g, '').replace(/^\+/, '');
  if (d.startsWith('0')) return `+62${d.slice(1)}`;
  return `+${d}`;
}

$('#reg-form').addEventListener('submit', async event => {
  event.preventDefault();
  showError('');
  if (!db) return showError('Isi URL dan publishable key Supabase di config.js terlebih dahulu.');
  const form = new FormData(event.currentTarget);
  const username = String(form.get('username')).trim();
  const phone = toE164(String(form.get('no_wa')));
  if (username.length < 3 || username.length > 50) return showError('Username harus 3-50 karakter.');
  if (!/^\+62[1-9]\d{7,12}$/.test(phone)) return showError('Format nomor WhatsApp tidak valid.');
  if (!form.get('persetujuan_pdp')) return showError('Persetujuan wajib dicentang.');
  const button = event.currentTarget.querySelector('button[type="submit"]');
  button.disabled = true;
  const { error } = await db.rpc('register_member', { p_username: username, p_phone: phone, p_consent: true });
  button.disabled = false;
  if (error) return showError(error.code === '23505' ? 'Nomor WhatsApp sudah terdaftar.' : `Pendaftaran gagal: ${error.message}`);
  $('#reg-box').hidden = true;
  $('#reg-success').hidden = false;
});
