<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ลงทะเบียนคนขับ — Kyokuyo Bus</title>
    <script src="https://static.line-scdn.net/liff/edge/versions/2.22.3/sdk.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0fdf4; }
        .input-field {
            width: 100%; border: 2px solid #e5e7eb; border-radius: 12px;
            padding: 12px 16px; font-size: 15px; outline: none;
            transition: border-color 0.2s; background: white;
        }
        .input-field:focus { border-color: #16a34a; }
        .btn-submit {
            width: 100%; padding: 16px; background: #16a34a; color: white;
            border: none; border-radius: 14px; font-size: 16px; font-weight: 700;
            cursor: pointer; transition: background 0.2s, transform 0.1s;
        }
        .btn-submit:active { transform: scale(0.98); background: #15803d; }
        .btn-submit:disabled { background: #86efac; cursor: not-allowed; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .spinner {
            width: 40px; height: 40px; border: 4px solid #dcfce7;
            border-top-color: #16a34a; border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
    </style>
</head>
<body>

{{-- Loading --}}
<div id="loading" class="flex flex-col items-center justify-center min-h-screen gap-4">
    <div class="spinner"></div>
    <p class="text-green-700 text-sm font-medium" id="loading-msg">กำลังตรวจสอบ LINE...</p>
</div>

{{-- Already registered & pending --}}
<div id="screen-pending" class="hidden flex flex-col items-center justify-center min-h-screen px-6 text-center">
    <div class="text-5xl mb-4">⏳</div>
    <h2 class="font-bold text-gray-800 text-lg mb-2">รอการอนุมัติ</h2>
    <p class="text-gray-500 text-sm" id="pending-name"></p>
    <p class="text-gray-400 text-xs mt-2">Admin กำลังตรวจสอบข้อมูลของคุณ</p>
</div>

{{-- Already approved --}}
<div id="screen-approved" class="hidden flex flex-col items-center justify-center min-h-screen px-6 text-center">
    <div class="text-5xl mb-4">✅</div>
    <h2 class="font-bold text-gray-800 text-lg mb-2">ลงทะเบียนแล้ว</h2>
    <p class="text-gray-500 text-sm mb-6" id="approved-name"></p>
    <a href="/driver/checkin" style="background:#16a34a; color:#fff;"
        class="px-6 py-3 rounded-xl font-bold text-sm">
        ไปหน้าสแกน →
    </a>
</div>

{{-- Error --}}
<div id="screen-error" class="hidden flex flex-col items-center justify-center min-h-screen px-6 text-center">
    <div class="text-5xl mb-4">⚠️</div>
    <h2 class="font-bold text-gray-800 text-lg mb-2">เกิดข้อผิดพลาด</h2>
    <p class="text-red-500 text-sm font-semibold px-4 py-2 bg-red-50 rounded-lg" id="error-msg"></p>
    <p class="text-gray-400 text-xs mt-3">กรุณาปิดแล้วเปิดใหม่ผ่านเมนู LINE</p>
</div>

{{-- Registration Form --}}
<div id="form-area" class="hidden min-h-screen pb-10">

    {{-- Header --}}
    <div style="background: linear-gradient(135deg, #16a34a, #0d9488);" class="px-5 pt-10 pb-8 text-center">
        <div class="w-14 h-14 rounded-2xl bg-white/20 flex items-center justify-center mx-auto mb-3">
            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
        </div>
        <h1 class="text-xl font-extrabold text-white">ลงทะเบียนคนขับ</h1>
        <p class="text-green-100 text-sm mt-1">Kyokuyo Bus System</p>
        <div class="mt-4 inline-flex items-center gap-2 bg-white/20 rounded-full px-4 py-2">
            <img id="line-avatar" src="" alt="" class="w-7 h-7 rounded-full object-cover hidden">
            <span class="text-white text-sm font-medium" id="line-name">—</span>
        </div>
    </div>

    <div class="px-4 mt-5 max-w-sm mx-auto space-y-4">

        <div id="form-error" class="hidden bg-red-50 border border-red-200 rounded-xl p-4">
            <p class="text-red-700 text-sm" id="form-error-msg"></p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 space-y-5">

            <input type="hidden" id="line_user_id">
            <input type="hidden" id="line_display_name">

            {{-- ชื่อ --}}
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1.5">
                    ชื่อ-นามสกุล <span class="text-red-500">*</span>
                </label>
                <input type="text" id="driver_name" placeholder="กรอกชื่อ-นามสกุล" class="input-field">
            </div>

            {{-- เบอร์โทร --}}
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1.5">
                    เบอร์โทรศัพท์ <span class="text-red-500">*</span>
                </label>
                <input type="tel" id="phone" placeholder="0xx-xxx-xxxx" class="input-field">
            </div>

            {{-- เลือกรถ --}}
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">
                    รถที่คุณขับได้ <span class="text-red-500">*</span>
                    <span class="text-xs font-normal text-gray-400">(เลือกได้มากกว่า 1 คัน)</span>
                </label>

                @foreach($busesByRoute as $routeName => $buses)
                <div class="mb-3">
                    <p class="text-xs font-bold text-green-700 bg-green-50 rounded-lg px-3 py-1.5 mb-2">
                        🚌 {{ $routeName }}
                    </p>
                    <div class="space-y-2 pl-2">
                        @foreach($buses as $bus)
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="bus_ids" value="{{ $bus->bus_id }}"
                                class="w-5 h-5 rounded accent-green-600 flex-shrink-0">
                            <span class="text-sm text-gray-700 group-hover:text-green-700 transition">
                                {{ $bus->plate_no }}
                                @if($bus->bus_no)
                                    <span class="text-gray-400">({{ $bus->bus_no }})</span>
                                @endif
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            <button type="button" onclick="submitRegister()" class="btn-submit" id="submit-btn">
                ลงทะเบียน
            </button>
        </div>

        <p class="text-center text-xs text-gray-400 pb-4">Kyokuyo Industrial (Thailand) Co., Ltd.</p>
    </div>
</div>

<script>
const LIFF_ID = "{{ config('services.line.liff_register_id') }}";
const CSRF    = document.querySelector('meta[name="csrf-token"]').content;

function show(id) {
    ['loading','screen-pending','screen-approved','screen-error','form-area']
        .forEach(s => document.getElementById(s).classList.add('hidden'));
    document.getElementById(id).classList.remove('hidden');
}

function showErr(step, msg) {
    document.getElementById('error-msg').textContent = '[' + step + '] ' + (msg || 'Unknown error');
    show('screen-error');
    console.error(step, msg);
}

async function init() {
    // ── Step 0: เช็ค PHP session ก่อน (ข้าม LIFF ได้เลย) ─────
    try {
        const r = await fetch('/driver/session-check');
        const d = await r.json();
        if (d.success) {
            document.getElementById('line_user_id').value      = d.line_user_id;
            document.getElementById('line_display_name').value = d.display_name || '';
            document.getElementById('line-name').textContent   = d.display_name || d.driver_name;
            if (d.is_approved) {
                document.getElementById('approved-name').textContent = 'สวัสดี ' + d.driver_name;
                show('screen-approved');
            } else {
                document.getElementById('pending-name').textContent = 'สวัสดี ' + d.driver_name;
                show('screen-pending');
            }
            return;
        }
    } catch(_) {}

    // ── Step 1: liff.init (auto-retry ถ้ามี stale token) ──────
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
        showErr('liff.init', e.message || JSON.stringify(e));
        return;
    }

    // ── Step 2: login / getProfile ─────────────────────────────
    document.getElementById('loading-msg').textContent = 'กำลังดึงข้อมูลโปรไฟล์...';
    if (!liff.isLoggedIn()) {
        liff.login({ redirectUri: window.location.href });
        return;
    }

    let profile;
    try {
        profile = await liff.getProfile();
    } catch(e) {
        showErr('liff.getProfile', e.message || JSON.stringify(e));
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

    // ── Step 3: check-status ───────────────────────────────────
    document.getElementById('loading-msg').textContent = 'กำลังตรวจสอบข้อมูล...';
    let data;
    try {
        const res = await fetch('/driver/check-status', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ line_user_id: profile.userId })
        });
        data = await res.json();
    } catch(e) {
        showErr('check-status fetch', e.message || JSON.stringify(e));
        return;
    }

    if (data.status === 'approved') {
        document.getElementById('approved-name').textContent = 'สวัสดี ' + data.driver_name;
        show('screen-approved');
    } else if (data.status === 'pending') {
        document.getElementById('pending-name').textContent = 'สวัสดี ' + data.driver_name;
        show('screen-pending');
    } else {
        show('form-area');
    }
}

async function submitRegister() {
    const lineUserId = document.getElementById('line_user_id').value;
    const driverName = document.getElementById('driver_name').value.trim();
    const phone      = document.getElementById('phone').value.trim();
    const busIds     = [...document.querySelectorAll('input[name="bus_ids"]:checked')]
                         .map(el => parseInt(el.value));

    const errEl = document.getElementById('form-error');
    const errMsg = document.getElementById('form-error-msg');
    errEl.classList.add('hidden');

    if (!driverName) { errMsg.textContent = 'กรุณากรอกชื่อ-นามสกุล'; errEl.classList.remove('hidden'); return; }
    if (!phone)      { errMsg.textContent = 'กรุณากรอกเบอร์โทรศัพท์'; errEl.classList.remove('hidden'); return; }
    if (busIds.length === 0) { errMsg.textContent = 'กรุณาเลือกรถที่คุณขับได้อย่างน้อย 1 คัน'; errEl.classList.remove('hidden'); return; }

    const btn = document.getElementById('submit-btn');
    btn.disabled = true;
    btn.textContent = 'กำลังบันทึก...';

    try {
        const res  = await fetch('/register-driver', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({
                line_user_id:      lineUserId,
                line_display_name: document.getElementById('line_display_name').value,
                driver_name:       driverName,
                phone,
                bus_ids:           busIds,
            })
        });
        const data = await res.json();

        if (data.status === 'registered') {
            document.getElementById('pending-name').textContent = 'สวัสดี ' + driverName;
            show('screen-pending');
        } else if (data.status === 'approved') {
            document.getElementById('approved-name').textContent = 'สวัสดี ' + driverName;
            show('screen-approved');
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
