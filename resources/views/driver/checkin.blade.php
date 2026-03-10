<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>บันทึกเที่ยววิ่ง</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://static.line-scdn.net/liff/edge/versions/2.22.3/sdk.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; }
        .bus-option input { display: none; }
        .bus-option input:checked + .bus-card {
            border-color: #16a34a; background-color: #f0fdf4; color: #15803d;
        }
        .bus-card { transition: all 0.15s; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .spinner { animation: spin 0.8s linear infinite; }
        .select-styled {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2316a34a' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 12px center; padding-right: 36px;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

{{-- Loading --}}
<div id="loading" class="flex flex-col items-center justify-center min-h-screen gap-3">
    <svg class="w-10 h-10 text-green-500 spinner" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
    </svg>
    <p class="text-gray-500 text-sm" id="loading-msg">กำลังตรวจสอบ LINE...</p>
</div>

{{-- Error / Not registered --}}
<div id="screen-error" class="hidden flex flex-col items-center justify-center min-h-screen px-6 text-center">
    <div class="text-5xl mb-4">⚠️</div>
    <p class="font-bold text-gray-800 text-lg mb-2" id="error-title">เกิดข้อผิดพลาด</p>
    <p class="text-gray-500 text-sm mb-6" id="error-msg"></p>
    <a href="/register-driver" style="background:#16a34a; color:#fff;"
        class="px-6 py-3 rounded-xl font-bold text-sm">
        ไปลงทะเบียน →
    </a>
</div>

{{-- Main Form --}}
<div id="main-form" class="hidden max-w-sm mx-auto px-4 py-8">

    {{-- Header --}}
    <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-full mb-3" style="background:#16a34a;">
            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-extrabold text-gray-900">บันทึกเที่ยววิ่ง</h1>
        <p class="text-sm text-gray-400 mt-1" id="driver-greeting">กำลังโหลด...</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">

        {{-- เลือกสายรถ --}}
        <div>
            <p class="text-sm font-bold text-gray-700 mb-2">สายรถวันนี้ <span class="text-red-500">*</span></p>
            <select id="route-select" onchange="filterBusesByRoute(this.value)"
                class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm font-semibold
                       focus:border-green-500 focus:outline-none select-styled bg-white">
                <option value="">— เลือกสายรถ —</option>
            </select>
        </div>

        {{-- เลือกทะเบียนรถ --}}
        <div>
            <p class="text-sm font-bold text-gray-700 mb-2">ทะเบียนรถ <span class="text-red-500">*</span></p>
            <select id="bus-select"
                class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm font-semibold
                       focus:border-green-500 focus:outline-none select-styled bg-white">
                <option value="">— เลือกสายรถก่อน —</option>
            </select>
        </div>

        {{-- ประเภทรถ --}}
        <div>
            <p class="text-sm font-bold text-gray-700 mb-3">ประเภทรถวันนี้</p>
            <div class="grid grid-cols-2 gap-3">
                <label class="bus-option cursor-pointer">
                    <input type="radio" name="bus_type" value="Bus" checked>
                    <div class="bus-card border-2 border-gray-200 rounded-xl p-4 text-center">
                        <div class="text-3xl mb-1">🚌</div>
                        <p class="font-bold text-sm">รถบัส</p>
                    </div>
                </label>
                <label class="bus-option cursor-pointer">
                    <input type="radio" name="bus_type" value="Van">
                    <div class="bus-card border-2 border-gray-200 rounded-xl p-4 text-center">
                        <div class="text-3xl mb-1">🚐</div>
                        <p class="font-bold text-sm">รถตู้</p>
                    </div>
                </label>
            </div>
        </div>

        {{-- จำนวนพนักงาน --}}
        <div>
            <p class="text-sm font-bold text-gray-700 mb-3">จำนวนพนักงาน (คน)</p>
            <div class="flex items-center justify-center gap-3">
                <button type="button" onclick="adjustCount(-1)"
                    class="w-14 h-14 rounded-xl bg-gray-100 text-gray-600 text-2xl font-bold
                           hover:bg-gray-200 active:scale-95 transition flex items-center justify-center">−</button>
                <input type="number" id="passenger_count" value="0" min="0"
                    style="width:120px;"
                    class="text-4xl font-extrabold text-center border-2 border-gray-200
                           rounded-xl py-3 focus:border-green-500 focus:outline-none">
                <button type="button" onclick="adjustCount(1)"
                    class="w-14 h-14 rounded-xl bg-gray-100 text-gray-600 text-2xl font-bold
                           hover:bg-gray-200 active:scale-95 transition flex items-center justify-center">+</button>
            </div>
        </div>

        {{-- ปุ่มสแกน --}}
        <button id="scan-btn" onclick="startScan()"
            style="background-color:#16a34a; color:#ffffff;"
            class="w-full py-4 rounded-xl text-lg font-extrabold shadow-md
                   active:scale-95 transition-all flex items-center justify-center gap-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4
                       m12 0h.01M5 8H3m2 4H3m18-4h-2M5 20H3"/>
            </svg>
            สแกน QR Code
        </button>

    </div>

    {{-- Status --}}
    <div id="status-box" class="hidden mt-4 rounded-2xl p-5 text-center">
        <p id="status-icon" class="text-4xl mb-2"></p>
        <p id="status-msg" class="font-bold text-lg"></p>
        <p id="status-sub" class="text-sm mt-1 opacity-70"></p>
    </div>

    <div class="mt-6 text-center">
        <a href="/driver/profile" class="text-sm text-green-600 font-semibold hover:underline">
            ดูประวัติของฉัน →
        </a>
    </div>

</div>

<script>
const CSRF    = document.querySelector('meta[name="csrf-token"]').content;
const LIFF_ID = "{{ config('services.line.liff_checkin_id') }}";

let lineUserId = null;
let allBuses   = [];  // รถทั้งหมดของ driver คนนี้

function show(id) {
    ['loading','screen-error','main-form']
        .forEach(s => document.getElementById(s).classList.add('hidden'));
    document.getElementById(id).classList.remove('hidden');
}

function showError(title, msg, showRegisterBtn = true) {
    document.getElementById('error-title').textContent = title;
    document.getElementById('error-msg').textContent   = msg;
    const regBtn = document.querySelector('#screen-error a');
    regBtn.style.display = showRegisterBtn ? '' : 'none';
    show('screen-error');
}

// ── โหลดรถของ driver (ใช้ได้ทั้งจาก session และ LIFF) ───────────────────────
async function loadBuses(driverId, driverName) {
    document.getElementById('driver-greeting').textContent = 'สวัสดี ' + driverName;
    document.getElementById('loading-msg').textContent = 'กำลังโหลดข้อมูลรถ...';
    let busData;
    try {
        const busRes = await fetch('/driver/my-buses', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ line_user_id: driverId })
        });
        busData = await busRes.json();
    } catch(e) {
        showError('[my-buses] ' + (e.message || JSON.stringify(e)), '', false);
        return;
    }
    if (!busData.success) {
        showError('ไม่พบข้อมูลรถ', busData.message, false);
        return;
    }
    allBuses = busData.buses;
    buildRouteDropdown();
    show('main-form');
}

// ── LIFF Init ────────────────────────────────────────────────────────────────
async function init() {
    // Step 0: เช็ค PHP session ก่อน (ข้าม LIFF ได้เลย)
    try {
        const r = await fetch('/driver/session-check');
        const d = await r.json();
        if (d.success && d.is_approved) {
            lineUserId = d.line_user_id;
            await loadBuses(lineUserId, d.display_name || d.driver_name);
            return;
        }
    } catch(_) {}

    // Step 1: liff.init (auto-retry ถ้ามี stale token)
    document.getElementById('loading-msg').textContent = 'กำลังเชื่อมต่อ LINE...';
    try {
        await liff.init({ liffId: LIFF_ID });
        sessionStorage.removeItem('liff_retry');
    } catch(e) {
        if (!sessionStorage.getItem('liff_retry')) {
            sessionStorage.setItem('liff_retry', '1');
            // ล้าง LIFF token ใน localStorage โดยตรง (liff.logout() ใช้ไม่ได้ตอน init ยังไม่ผ่าน)
            try {
                Object.keys(localStorage)
                    .filter(k => k.startsWith('LIFF_STORE'))
                    .forEach(k => localStorage.removeItem(k));
            } catch(_) {}
            location.reload();
            return;
        }
        sessionStorage.removeItem('liff_retry');
        showError('[liff.init] ' + (e.message || JSON.stringify(e)), '', false);
        return;
    }

    // Step 2: login / getProfile
    document.getElementById('loading-msg').textContent = 'กำลังดึงข้อมูลโปรไฟล์...';
    if (!liff.isLoggedIn()) {
        liff.login({ redirectUri: window.location.href });
        return;
    }

    let profile;
    try {
        profile = await liff.getProfile();
    } catch(e) {
        showError('[liff.getProfile] ' + (e.message || JSON.stringify(e)), '', false);
        return;
    }
    lineUserId = profile.userId;

    // Step 3: check-status
    document.getElementById('loading-msg').textContent = 'กำลังตรวจสอบสถานะ...';
    let statusData;
    try {
        const statusRes = await fetch('/driver/check-status', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ line_user_id: lineUserId })
        });
        statusData = await statusRes.json();
    } catch(e) {
        showError('[check-status] ' + (e.message || JSON.stringify(e)), '', false);
        return;
    }

    if (statusData.status === 'new') {
        showError('ยังไม่ได้ลงทะเบียน', 'กรุณาลงทะเบียนก่อนใช้งาน');
        return;
    }
    if (statusData.status === 'pending') {
        showError('รอการอนุมัติ', 'Admin กำลังตรวจสอบข้อมูลของคุณ กรุณารอสักครู่', false);
        return;
    }

    // Step 4: my-buses
    await loadBuses(lineUserId, profile.displayName);
}

// ── Build route dropdown จากรถของ driver ────────────────────────────────────
function buildRouteDropdown() {
    const routeSet = new Map();
    allBuses.forEach(b => {
        if (!routeSet.has(b.route_id)) {
            routeSet.set(b.route_id, b.Route_Name);
        }
    });

    const sel = document.getElementById('route-select');
    sel.innerHTML = '<option value="">— เลือกสายรถ —</option>';
    routeSet.forEach((name, id) => {
        sel.innerHTML += `<option value="${id}">${name}</option>`;
    });

    // ถ้ามีสายเดียว เลือกให้เลย
    if (routeSet.size === 1) {
        const onlyRouteId = routeSet.keys().next().value;
        sel.value = onlyRouteId;
        filterBusesByRoute(onlyRouteId);
    }
}

function filterBusesByRoute(routeId) {
    const sel  = document.getElementById('bus-select');
    const list = allBuses.filter(b => String(b.route_id) === String(routeId));

    sel.innerHTML = '<option value="">— เลือกทะเบียนรถ —</option>';
    list.forEach(b => {
        sel.innerHTML += `<option value="${b.bus_id}">${b.plate_no}${b.bus_no ? ' (' + b.bus_no + ')' : ''}</option>`;
    });

    if (list.length === 1) {
        sel.value = list[0].bus_id;
    }
}

// ── QR Scan ──────────────────────────────────────────────────────────────────
function adjustCount(delta) {
    const input = document.getElementById('passenger_count');
    input.value = Math.max(0, (parseInt(input.value) || 0) + delta);
}

function showStatus(type, icon, msg, sub = '') {
    const box    = document.getElementById('status-box');
    const colors = {
        success: 'bg-green-50 border border-green-200 text-green-800',
        error:   'bg-red-50 border border-red-200 text-red-800',
        loading: 'bg-blue-50 border border-blue-200 text-blue-800',
    };
    box.className = `mt-4 rounded-2xl p-5 text-center ${colors[type]}`;
    document.getElementById('status-icon').textContent = icon;
    document.getElementById('status-msg').textContent  = msg;
    document.getElementById('status-sub').textContent  = sub;
    box.classList.remove('hidden');
}

function resetBtn() {
    const btn = document.getElementById('scan-btn');
    btn.disabled = false;
    btn.innerHTML = `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8H3m2 4H3m18-4h-2M5 20H3"/>
    </svg> สแกน QR Code`;
}

async function startScan() {
    const busId = document.getElementById('bus-select').value;
    const count = parseInt(document.getElementById('passenger_count').value);

    if (!busId)        { showStatus('error', '⚠️', 'กรุณาเลือกทะเบียนรถ'); return; }
    if (!count || count <= 0) { showStatus('error', '⚠️', 'กรุณากรอกจำนวนพนักงาน'); return; }

    const btn = document.getElementById('scan-btn');
    btn.disabled = true;
    btn.innerHTML = `<svg class="w-5 h-5 spinner" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
    </svg> กำลังเปิดกล้อง...`;

    try {
        const result = await liff.scanCodeV2();
        if (result?.value) {
            sendData(result.value, busId, count);
        } else {
            showStatus('error', '❌', 'ไม่พบข้อมูล QR Code');
            resetBtn();
        }
    } catch(e) {
        showStatus('error', '❌', 'ยกเลิกการสแกน', e.message);
        resetBtn();
    }
}

function sendData(token, busId, count) {
    showStatus('loading', '⏳', 'กำลังบันทึกข้อมูล...');

    fetch('/api/driver/checkin', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({
            token,
            bus_id:          busId,
            bus_type:        document.querySelector('input[name="bus_type"]:checked').value,
            passenger_count: count,
            line_user_id:    lineUserId,
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            showStatus('success', '✅', 'บันทึกสำเร็จ!', data.message);
            document.getElementById('main-form').querySelector('.bg-white').classList.add('opacity-50', 'pointer-events-none');
            setTimeout(() => liff.closeWindow(), 2500);
        } else {
            showStatus('error', '❌', 'เกิดข้อผิดพลาด', data.message);
            resetBtn();
        }
    })
    .catch(() => {
        showStatus('error', '❌', 'ไม่สามารถเชื่อมต่อได้', 'กรุณาลองใหม่');
        resetBtn();
    });
}

init();
</script>
</body>
</html>
