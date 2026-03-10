@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="https://static.line-scdn.net/liff/edge/versions/2.22.3/sdk.js"></script>

<div class="min-h-screen bg-gray-50 pb-10">

    <div id="loading" class="flex flex-col items-center justify-center min-h-screen">
        <div class="w-12 h-12 border-4 border-green-500 border-t-transparent rounded-full animate-spin mb-4"></div>
        <p class="text-gray-500 text-sm" id="loading-msg">กำลังโหลดข้อมูล...</p>
    </div>

    <div id="screen-error" class="hidden flex flex-col items-center justify-center min-h-screen px-6 text-center">
        <div class="text-5xl mb-4">⚠️</div>
        <h2 class="font-bold text-gray-800 text-lg mb-2" id="error-title">ไม่พบข้อมูล</h2>
        <p class="text-gray-500 text-sm mb-6" id="error-msg"></p>
        <a href="/register-driver" style="background:#16a34a; color:#fff;"
            class="px-6 py-3 rounded-xl font-bold text-sm">
            ไปลงทะเบียน →
        </a>
    </div>

    <div id="content" class="hidden">

        <div class="px-4 pt-8 pb-6 text-center"
            style="background: linear-gradient(135deg, #16a34a, #0d9488);">
            <img id="line-avatar" src="" alt="" class="w-16 h-16 rounded-full object-cover mx-auto mb-3 hidden border-2 border-white/50">
            <div id="line-avatar-placeholder" class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center mx-auto mb-3">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <h1 class="text-xl font-extrabold text-white" id="display-name">—</h1>
            <div class="mt-3" id="display-status"></div>
        </div>

        <div class="px-4 mt-4 space-y-4 max-w-sm mx-auto">

            {{-- ข้อมูลส่วนตัว --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <h2 class="text-sm font-bold text-gray-700 mb-3">ข้อมูลการลงทะเบียน</h2>
                <div class="space-y-2.5 text-sm" id="info-list"></div>
            </div>

            {{-- รถที่ลงทะเบียนไว้ --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <h2 class="text-sm font-bold text-gray-700 mb-3">รถที่ลงทะเบียนไว้</h2>
                <div id="buses-list"></div>
            </div>

            {{-- สรุปรายเดือน --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <h2 class="text-sm font-bold text-gray-700 mb-3">สรุปรายเดือน</h2>
                <div id="monthly-list"></div>
            </div>

            {{-- ประวัติสแกน --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <h2 class="text-sm font-bold text-gray-700 mb-3">
                    ประวัติการสแกน QR
                    <span class="text-xs text-gray-400 font-normal">(30 รายการล่าสุด)</span>
                </h2>
                <div id="log-list"></div>
            </div>

        </div>
    </div>

</div>

<script>
const CSRF    = document.querySelector('meta[name="csrf-token"]').content;
const LIFF_ID = "{{ config('services.line.liff_profile_id') }}";

function showError(title, msg) {
    document.getElementById('loading').classList.add('hidden');
    document.getElementById('error-title').textContent = title;
    document.getElementById('error-msg').textContent   = msg;
    document.getElementById('screen-error').classList.remove('hidden');
}

async function init() {
    // 1. เช็ค PHP session ก่อน (เร็วที่สุด)
    try {
        const r = await fetch('/driver/profile-session');
        const d = await r.json();
        if (d.success) { renderProfile(d); return; }
    } catch(e) {}

    // 2. ลอง LIFF
    document.getElementById('loading-msg').textContent = 'กำลังตรวจสอบ LINE...';
    try {
        // liff.init: auto-retry ถ้ามี stale token
        try {
            await liff.init({ liffId: LIFF_ID });
            sessionStorage.removeItem('liff_retry');
        } catch(initErr) {
            if (!sessionStorage.getItem('liff_retry')) {
                sessionStorage.setItem('liff_retry', '1');
                // ล้าง LIFF token ใน localStorage โดยตรง
                try {
                    Object.keys(localStorage)
                        .filter(k => k.startsWith('LIFF_STORE'))
                        .forEach(k => localStorage.removeItem(k));
                } catch(_) {}
                location.reload();
                return;
            }
            sessionStorage.removeItem('liff_retry');
            throw initErr; // ส่งต่อให้ catch ด้านนอก
        }

        if (!liff.isLoggedIn()) {
            liff.login({ redirectUri: window.location.href });
            return;
        }

        const profile = await liff.getProfile();

        if (profile.pictureUrl) {
            const av = document.getElementById('line-avatar');
            av.src = profile.pictureUrl;
            av.classList.remove('hidden');
            document.getElementById('line-avatar-placeholder').classList.add('hidden');
        }

        document.getElementById('loading-msg').textContent = 'กำลังโหลดประวัติ...';
        const res  = await fetch('/driver/line-login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ line_user_id: profile.userId })
        });
        const data = await res.json();

        if (!data.success) {
            showError('ไม่พบข้อมูล', data.message ?? 'กรุณาลงทะเบียนก่อน');
            return;
        }

        renderProfile(data);

    } catch(e) {
        showError('เกิดข้อผิดพลาด', e.message || 'ไม่สามารถเชื่อมต่อได้');
    }
}

function renderProfile(data) {
    const d = data.driver;

    document.getElementById('display-name').textContent = d.driver_name || '—';
    document.getElementById('display-status').innerHTML = d.is_approved
        ? `<span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-sm font-bold bg-white text-green-700">✅ อนุมัติแล้ว</span>`
        : `<span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-sm font-bold bg-orange-100 text-orange-700">⏳ รอการอนุมัติ</span>`;

    document.getElementById('info-list').innerHTML = `
        <div class="flex justify-between"><span class="text-gray-400">ชื่อ</span><span class="font-semibold">${d.driver_name||'-'}</span></div>
        <div class="flex justify-between"><span class="text-gray-400">เบอร์โทร</span><span class="font-semibold">${d.phone||'-'}</span></div>
        <div class="flex justify-between"><span class="text-gray-400">วันที่สมัคร</span><span class="font-semibold text-xs">${d.created_at ? d.created_at.substring(0,10) : '-'}</span></div>
    `;

    // รถที่ลงทะเบียนไว้
    const busEl = document.getElementById('buses-list');
    busEl.innerHTML = (data.buses || []).length === 0
        ? '<p class="text-gray-400 text-sm text-center py-2">ยังไม่มีรถ</p>'
        : data.buses.map(b => `
            <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                <span class="font-semibold text-sm text-gray-800">🚌 ${b.plate_no}${b.bus_no ? ' (' + b.bus_no + ')' : ''}</span>
                <span class="text-xs text-blue-600 font-medium">${b.Route_Name||'-'}</span>
            </div>`).join('');

    // Monthly
    const monthlyEl = document.getElementById('monthly-list');
    monthlyEl.innerHTML = (data.monthly||[]).length === 0
        ? '<p class="text-gray-400 text-center py-4 text-sm">ยังไม่มีข้อมูล</p>'
        : data.monthly.map(m => `
            <div class="bg-gray-50 rounded-xl p-3 mb-2">
                <div class="flex justify-between items-center mb-1">
                    <span class="font-bold text-gray-700 text-sm">${m.month}</span>
                    <span class="text-green-600 font-extrabold text-sm">${Number(m.total_amount||0).toLocaleString()} ฿</span>
                </div>
                <div class="flex gap-4 text-xs text-gray-500">
                    <span>🚌 ${m.total_trips} เที่ยว</span>
                    <span>👥 ${m.total_passengers} คน</span>
                </div>
            </div>`).join('');

    // Logs
    const logEl = document.getElementById('log-list');
    logEl.innerHTML = (data.logs||[]).length === 0
        ? '<p class="text-gray-400 text-center py-4 text-sm">ยังไม่มีประวัติการสแกน</p>'
        : data.logs.map(l => `
            <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
                <div>
                    <p class="text-sm font-semibold text-gray-800">
                        ${l.actual_bus_type==='Van'?'🚐':'🚌'} ${l.factory_id}
                        <span class="text-xs font-normal text-gray-400 ml-1">${l.shift==='Day'?'☀️':'🌙'}</span>
                    </p>
                    <p class="text-xs text-blue-500 mt-0.5">${l.Route_Name||'-'} · ${l.plate_no||'-'}</p>
                    <p class="text-xs text-gray-400">${l.log_date_time}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-bold text-gray-800">${Number(l.applied_price||0).toLocaleString()} ฿</p>
                    <p class="text-xs text-gray-500">${l.passenger_count} คน</p>
                </div>
            </div>`).join('');

    document.getElementById('loading').classList.add('hidden');
    document.getElementById('content').classList.remove('hidden');
}

init();
</script>
@endsection
