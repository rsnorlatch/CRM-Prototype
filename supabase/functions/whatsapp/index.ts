import { createClient } from 'https://esm.sh/@supabase/supabase-js@2';

const corsHeaders = {
  'Access-Control-Allow-Origin': '*',
  'Access-Control-Allow-Headers': 'authorization, x-client-info, apikey, content-type',
  'Access-Control-Allow-Methods': 'POST, OPTIONS',
};
const batchSize = 50;

function response(body: unknown, status = 200) {
  return new Response(JSON.stringify(body), {
    status,
    headers: { ...corsHeaders, 'Content-Type': 'application/json' },
  });
}

function requiredEnv(name: string) {
  const value = Deno.env.get(name);
  if (!value) throw new Error(`Supabase Edge Function secret ${name} belum diatur.`);
  return value;
}

function isValidPhone(phone: string) {
  return /^\+[1-9]\d{7,14}$/.test(phone);
}

async function sendTemplate(
  phone: string,
  templateName: string,
  language: string,
  parameters: string[],
) {
  if (!isValidPhone(phone)) throw new Error('Nomor WhatsApp bukan format internasional E.164.');
  const token = requiredEnv('WHATSAPP_ACCESS_TOKEN');
  const phoneNumberId = requiredEnv('WHATSAPP_PHONE_NUMBER_ID');
  const version = requiredEnv('WHATSAPP_API_VERSION');
  const components = parameters.length
    ? [{
      type: 'body',
      parameters: parameters.map((text) => ({ type: 'text', text })),
    }]
    : [];
  const result = await fetch(`https://graph.facebook.com/${version}/${phoneNumberId}/messages`, {
    method: 'POST',
    headers: {
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      messaging_product: 'whatsapp',
      recipient_type: 'individual',
      to: phone.slice(1),
      type: 'template',
      template: {
        name: templateName,
        language: { code: language },
        ...(components.length ? { components } : {}),
      },
    }),
  });
  const payload = await result.json();
  if (!result.ok) {
    const providerMessage = payload?.error?.message;
    throw new Error(typeof providerMessage === 'string' ? providerMessage : `Provider WhatsApp menolak pesan (HTTP ${result.status}).`);
  }
  return payload?.messages?.[0]?.id as string | undefined;
}

Deno.serve(async (request) => {
  if (request.method === 'OPTIONS') return new Response('ok', { headers: corsHeaders });
  if (request.method !== 'POST') return response({ error: 'Metode tidak didukung.' }, 405);

  const authorization = request.headers.get('Authorization');
  if (!authorization) return response({ error: 'Sesi login diperlukan.' }, 401);

  let url: string;
  let anonKey: string;
  let serviceKey: string;
  try {
    url = requiredEnv('SUPABASE_URL');
    anonKey = requiredEnv('SUPABASE_ANON_KEY');
    serviceKey = requiredEnv('SUPABASE_SERVICE_ROLE_KEY');
  } catch (error) {
    return response({ error: error instanceof Error ? error.message : 'Konfigurasi Supabase belum lengkap.' }, 500);
  }
  const userClient = createClient(url, anonKey, {
    global: { headers: { Authorization: authorization } },
    auth: { persistSession: false, autoRefreshToken: false },
  });
  const { data: { user }, error: authError } = await userClient.auth.getUser();
  if (authError || !user) return response({ error: 'Sesi login tidak valid.' }, 401);

  const adminClient = createClient(url, serviceKey, {
    auth: { persistSession: false, autoRefreshToken: false },
  });
  const { data: profile, error: profileError } = await adminClient
    .from('profiles').select('role').eq('id', user.id).single();
  if (profileError) return response({ error: 'Peran pengguna tidak dapat diverifikasi.' }, 500);
  if (profile.role !== 'admin') return response({ error: 'Akses hanya untuk admin.' }, 403);

  let body: Record<string, unknown>;
  try {
    body = await request.json();
  } catch {
    return response({ error: 'Body permintaan harus berupa JSON.' }, 400);
  }

  if (body.action === 'order_status') {
    const orderId = typeof body.order_id === 'string' ? body.order_id : '';
    const templateName = typeof body.template_name === 'string' ? body.template_name : '';
    const language = typeof body.template_language === 'string' ? body.template_language : 'id';
    if (!/^[0-9a-f-]{36}$/i.test(orderId)
      || !/^[A-Za-z0-9_]{1,100}$/.test(templateName)
      || !/^[A-Za-z0-9_-]{1,20}$/.test(language)) {
      return response({ error: 'Pesanan atau nama template tidak valid.' }, 400);
    }
    const { data: order, error: orderError } = await adminClient
      .from('preorders')
      .select('id, status, profiles(full_name, phone)')
      .eq('id', orderId)
      .single();
    if (orderError || !order) return response({ error: 'Pesanan tidak ditemukan.' }, 404);
    const customer = Array.isArray(order.profiles) ? order.profiles[0] : order.profiles;
    if (!customer?.phone) return response({ error: 'Nomor WhatsApp pelanggan tidak tersedia.' }, 422);
    try {
      const readableStatus: Record<string, string> = {
        pending: 'Menunggu konfirmasi',
        confirmed: 'Dikonfirmasi',
        preparing: 'Sedang disiapkan',
        ready: 'Siap diambil',
        completed: 'Selesai',
        cancelled: 'Dibatalkan',
      };
      const messageId = await sendTemplate(
        customer.phone,
        templateName,
        language,
        [customer.full_name, readableStatus[order.status] ?? order.status],
      );
      return response({ sent: true, message_id: messageId });
    } catch (error) {
      const detail = error instanceof Error ? error.message : 'Pengiriman WhatsApp gagal.';
      return response({ error: detail }, 502);
    }
  }

  if (body.action !== 'broadcast' || typeof body.campaign_id !== 'string'
      || !/^[0-9a-f-]{36}$/i.test(body.campaign_id)) {
    return response({ error: 'Aksi atau ID kampanye tidak valid.' }, 400);
  }
  const campaignId = body.campaign_id;
  const { data: campaign, error: campaignError } = await adminClient
    .from('broadcast_campaigns')
    .select('id, created_by, template_name, template_language, template_parameters, status')
    .eq('id', campaignId)
    .single();
  if (campaignError || !campaign || campaign.created_by !== user.id) {
    return response({ error: 'Kampanye tidak ditemukan atau bukan milik admin ini.' }, 404);
  }
  if (campaign.status === 'completed') return response({ sent: 0, failed: 0, remaining: 0 });
  if (campaign.status === 'queued') {
    const { error: updateError } = await adminClient.from('broadcast_campaigns')
      .update({ status: 'processing' }).eq('id', campaignId).eq('status', 'queued');
    if (updateError) return response({ error: `Status kampanye gagal diperbarui: ${updateError.message}` }, 500);
  }

  const { data: deliveries, error: deliveryError } = await adminClient.rpc('claim_broadcast_batch', {
    p_campaign_id: campaignId,
    p_limit: batchSize,
  });
  if (deliveryError) return response({ error: `Daftar penerima gagal dimuat: ${deliveryError.message}` }, 500);

  let sent = 0;
  let failed = 0;
  const parameters = Array.isArray(campaign.template_parameters)
    ? campaign.template_parameters.filter((value: unknown): value is string => typeof value === 'string')
    : [];
  for (const delivery of deliveries ?? []) {
    let providerMessageId: string | undefined;
    let errorMessage: string | null = null;
    if (delivery.whatsapp_consent !== true) {
      errorMessage = 'Persetujuan promosi tidak lagi aktif; pesan tidak dikirim.';
    } else if (!delivery.phone) {
      errorMessage = 'Nomor WhatsApp pelanggan tidak tersedia.';
    } else {
      try {
        providerMessageId = await sendTemplate(
          delivery.phone,
          campaign.template_name,
          campaign.template_language,
          parameters,
        );
      } catch (error) {
        errorMessage = error instanceof Error ? error.message : 'Pengiriman WhatsApp gagal.';
      }
    }
    const status = errorMessage ? 'failed' : 'sent';
    const { error: saveError } = await adminClient.from('broadcast_deliveries')
      .update({
        status,
        provider_message_id: providerMessageId ?? null,
        error_message: errorMessage,
        attempted_at: new Date().toISOString(),
      })
      .eq('id', delivery.delivery_id);
    if (saveError) return response({ error: `Hasil pengiriman tidak dapat disimpan: ${saveError.message}` }, 500);
    if (errorMessage) failed += 1;
    else sent += 1;
  }

  const { count: remaining, error: remainingError } = await adminClient
    .from('broadcast_deliveries')
    .select('id', { count: 'exact', head: true })
    .eq('campaign_id', campaignId)
    .in('status', ['queued', 'processing']);
  if (remainingError) return response({ error: `Status penerima belum dapat dihitung: ${remainingError.message}` }, 500);
  if (!remaining) {
    const { error: completeError } = await adminClient.from('broadcast_campaigns')
      .update({ status: 'completed' }).eq('id', campaignId);
    if (completeError) return response({ error: `Kampanye terkirim, tetapi status akhir gagal disimpan: ${completeError.message}` }, 500);
  }
  return response({ sent, failed, remaining: remaining ?? 0 });
});
