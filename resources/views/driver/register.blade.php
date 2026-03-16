<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลงทะเบียนคนขับ — Kyokuyo Bus</title>
    <script src="https://static.line-scdn.net/liff/edge/versions/2.22.3/sdk.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; background: #f0fdf4; }
        .input-field {
            width: 100%; border: 2px solid #e5e7eb; border-radius: 12px;
            padding: 12px 16px; font-size: 15px; outline: none;
            transition: border-color .2s; background: white;
        }
        .input-field:focus { border-color: #16a34a; }
        .spinner {
            width: 40px; height: 40px; border: 4px solid #dcfce7;
            border-top-color: #16a34a; border-radius: 50%;
            animation: spin .8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>

{{-- Loading --}}
<div id="loading" class="flex flex-col items-center justify-center min-h-screen gap-4">
    <div class="spinner"></div>
    <p id="loading-msg" class="text-green-700 text-sm font-medium">กำลังตรวจสอบ...</p>
</div>

{{-- Error Screen --}}
<div id="screen-error" class="hidden flex flex-col items-center justify-center min-h-screen text-center px-6">
    <div class="text-5xl mb-4">⚠️</div>
    <h2 class="font-bold text-lg text-gray-800">เกิดข้อผิดพลาด</h2>
    <p id="error-msg" class="text-red-500 text-sm mt-2"></p>
</div>

{{-- Registration Form --}}
<div id="form-area" class="hidden min-h-screen pb-10">
    <div class="px-4 pt-8 pb-4 text-center" style="background: linear-gradient(135deg, #16a34a, #0d9488);">
        <img id="line-avatar" src="" alt="" class="hidden w-16 h-16 rounded-full object-cover mx-auto mb-3 border-2 border-white/50">
        <h1 class="text-xl font-extrabold text-white">ลงทะเบียนคนขับ</h1>
        <p id="line-name" class="text-green-100 text-sm mt-1"></p>
    </div>

    <div class="px-4 mt-5 max-w-sm mx-auto space-y-4">
        <input type="hidden" id="line_user_id">
        <input type="hidden" id="line_display_name">

        <div>
            <label class="font-bold text-gray-700 text-sm block mb-1">ชื่อ-นามสกุล <span class="text-red-500">*</span></label>
            <input id="driver_name" class="input-field" placeholder="กรอกชื่อ-นามสกุล">
        </div>

        <div>
            <label class="font-bold text-gray-700 text-sm block mb-1">เบอร์โทรศัพท์ <span class="text-red-500">*</span></label>
            <input id="phone" class="input-field" type="tel" placeholder="08x-xxx-xxxx">
        </div>

        <div>
            <label class="font-bold text-gray-700 text-sm block mb-2">รถที่ขับได้ <span class="text-red-500">*</span></label>
            @foreach($busesByRoute as $routeName => $buses)
            <div class="mb-3">
                <p class="text-xs font-bold text-green-700 bg-green-50 rounded px-3 py-1 mb-2">{{ $routeName }}</p>
                @foreach($buses as $bus)
                <label class="flex items-center gap-2 py-1.5 cursor-pointer">
                    <input type="checkbox" name="bus_ids" value="{{ $bus->bus_id }}" class="w-4 h-4 accent-green-600">
                    <span class="text-sm text-gray-700">{{ $bus->plate_no }}{{ $bus->bus_no ? ' ('.$bus->bus_no.')' : '' }}</span>
                </label>
                @endforeach
            </div>
            @endforeach
        </div>

        <div id="form-error" class="hidden bg-red-50 border border-red-200 rounded-xl px-4 py-3">
            <p id="form-error-msg" class="text-red-600 text-sm"></p>
        </div>

        <button onclick="submitRegister()" id="submit-btn"
            class="w-full py-4 rounded-xl text-white font-extrabold text-base"
            style="background:#16a34a;">
            ลงทะเบียน
        </button>
    </div>
</div>

<script>
const LIFF_ID = "{{ config('services.line.liff_register_id') }}";
const DASHBOARD_URL = "{{ route('driver.dashboard') }}";

function show(id) {
    ['loading','screen-error','form-area']
        .forEach(s => document.getElementById(s).classList.add('hidden'));
    document.getElementById(id).classList.remove('hidden');
}

async function init() {
    // ── ชั้น 1: เช็ค cookie ผ่าน session-check ─────────────────────────────
    // ถ้า cookie ยังใช้ได้ → redirect ไป dashboard ทันที (ข้าม LIFF)
    try {
        const res  = await fetch('/driver/session-check');
        const data = await res.json();
        if (data.success) {
            window.location.href = DASHBOARD_URL;
            return;
        }
    } catch(_) {}

    // ── ชั้น 2: LIFF ────────────────────────────────────────────────────────
    document.getElementById('loading-msg').textContent = 'กำลังเชื่อมต่อ LINE...';
    try {
        await liff.init({ liffId: LIFF_ID });
    } catch(e) {
        document.getElementById('error-msg').textContent = e.message;
        show('screen-error');
        return;
    }

    if (!liff.isLoggedIn()) {
        liff.login({ redirectUri: window.location.href });
        return;
    }

    let profile;
    try {
        profile = await liff.getProfile();
    } catch(e) {
        document.getElementById('error-msg').textContent = 'ไม่สามารถอ่าน profile LINE';
        show('screen-error');
        return;
    }

    document.getElementById('line_user_id').value      = profile.userId;
    document.getElementById('line_display_name').value = profile.displayName;
    document.getElementById('line-name').textContent   = profile.displayName;
    if (profile.pictureUrl) {
        const av = document.getElementById('line-avatar');
        av.src = profile.pictureUrl;
        av.classList.remove('hidden');
    }

    // เช็คสถานะ driver
    document.getElementById('loading-msg').textContent = 'กำลังตรวจสอบข้อมูล...';
    try {
        const res  = await fetch('/driver/check-status', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ line_user_id: profile.userId })
        });
        const data = await res.json();

        if (data.status === 'approved' || data.status === 'pending') {
            // driver มีอยู่แล้ว → set cookie แล้ว redirect
            show('loading');
            document.getElementById('loading-msg').textContent = 'กำลังเข้าสู่ระบบ...';
            const authRes  = await fetch('/driver/auth', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ line_user_id: profile.userId })
            });
            const authData = await authRes.json();
            window.location.href = authData.redirect || DASHBOARD_URL;
        } else {
            // ใหม่ → แสดงฟอร์มลงทะเบียน
            show('form-area');
        }
    } catch(e) {
        document.getElementById('error-msg').textContent = e.message;
        show('screen-error');
    }
}

async function submitRegister() {
    const lineUserId = document.getElementById('line_user_id').value;
    const driverName = document.getElementById('driver_name').value.trim();
    const phone      = document.getElementById('phone').value.trim();
    const busIds     = [...document.querySelectorAll('input[name="bus_ids"]:checked')]
                         .map(el => parseInt(el.value));

    const errEl  = document.getElementById('form-error');
    const errMsg = document.getElementById('form-error-msg');
    errEl.classList.add('hidden');

    if (!driverName) { errMsg.textContent = 'กรุณากรอกชื่อ-นามสกุล'; errEl.classList.remove('hidden'); return; }
    if (!phone)      { errMsg.textContent = 'กรุณากรอกเบอร์โทรศัพท์'; errEl.classList.remove('hidden'); return; }
    if (busIds.length === 0) { errMsg.textContent = 'กรุณาเลือกรถที่ขับได้อย่างน้อย 1 คัน'; errEl.classList.remove('hidden'); return; }

    const btn = document.getElementById('submit-btn');
    btn.disabled = true;
    btn.textContent = 'กำลังบันทึก...';

    try {
        const res  = await fetch('/driver/register', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                line_user_id:      lineUserId,
                line_display_name: document.getElementById('line_display_name').value,
                driver_name:       driverName,
                phone,
                bus_ids:           busIds,
            })
        });
        const data = await res.json();

        if (data.redirect) {
            // cookie ถูก set ใน response แล้ว → redirect
            window.location.href = data.redirect;
        } else {
            errMsg.textContent = data.message || 'เกิดข้อผิดพลาด';
            errEl.classList.remove('hidden');
            btn.disabled = false;
            btn.textContent = 'ลงทะเบียน';
        }
    } catch(e) {
        errMsg.textContent = 'ไม่สามารถเชื่อมต่อได้';
        errEl.classList.remove('hidden');
        btn.disabled = false;
        btn.textContent = 'ลงทะเบียน';
    }
}

init();
</script>
</body>
</html>
