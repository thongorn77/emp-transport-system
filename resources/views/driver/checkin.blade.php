@extends('driver.layout')

@section('title', 'บันทึกเที่ยววิ่ง')

@push('head')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<style>
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
    #qr-reader video { width: 100% !important; }
    #qr-reader { width: 100% !important; }
</style>
@endpush

@section('content')

{{-- QR Scanner Modal --}}
<div id="qr-modal" class="hidden fixed inset-0 bg-black z-50 flex flex-col">
    <div class="flex justify-between items-center px-4 py-3 bg-black/80">
        <p class="text-white font-bold text-lg">สแกน QR Code</p>
        <button onclick="closeScanner()" class="text-white text-3xl leading-none">✕</button>
    </div>
    <div class="flex-1 flex items-center justify-center p-4">
        <div id="qr-reader" class="w-full max-w-sm rounded-xl overflow-hidden"></div>
    </div>
    <p class="text-gray-400 text-sm text-center pb-6">จ่อ QR Code ให้อยู่ในกรอบ</p>
</div>

@if(!$driver->is_approved)

{{-- Pending: not approved yet --}}
<div class="flex flex-col items-center justify-center min-h-64 text-center px-6 py-16">
    <div class="text-5xl mb-4">⏳</div>
    <p class="font-bold text-gray-800 text-lg">รอการอนุมัติจาก Admin</p>
    <p class="text-sm text-gray-500 mt-2">สวัสดี {{ $driver->driver_name }}<br>กรุณารอสักครู่</p>
</div>

@else

{{-- Main Form --}}
<div id="main-form" class="max-w-sm mx-auto px-4 py-8">

    <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-full mb-3" style="background:#16a34a;">
            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-extrabold text-gray-900">บันทึกเที่ยววิ่ง</h1>
        <p class="text-sm text-gray-400 mt-1">สวัสดี {{ $driver->driver_name }}</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">

        <div>
            <p class="text-sm font-bold text-gray-700 mb-2">สายรถวันนี้ <span class="text-red-500">*</span></p>
            <select id="route-select" onchange="filterBusesByRoute(this.value)"
                class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm font-semibold
                       focus:border-green-500 focus:outline-none select-styled bg-white">
                <option value="">— เลือกสายรถ —</option>
            </select>
        </div>

        <div>
            <p class="text-sm font-bold text-gray-700 mb-2">ทะเบียนรถ <span class="text-red-500">*</span></p>
            <select id="bus-select"
                class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm font-semibold
                       focus:border-green-500 focus:outline-none select-styled bg-white">
                <option value="">— เลือกสายรถก่อน —</option>
            </select>
        </div>

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

    <div id="status-box" class="hidden mt-4 rounded-2xl p-5 text-center">
        <p id="status-icon" class="text-4xl mb-2"></p>
        <p id="status-msg" class="font-bold text-lg"></p>
        <p id="status-sub" class="text-sm mt-1 opacity-70"></p>
    </div>

</div>

@endif
@endsection

@push('scripts')
<script>
const lineUserId = '{{ $driver->line_user_id }}';
let allBuses = [];
let html5QrCode = null;

// โหลดรถทันทีเมื่อหน้าเปิด
document.addEventListener('DOMContentLoaded', async () => {
    @if($driver->is_approved)
    await loadBuses();
    @endif
});

async function loadBuses() {
    try {
        const res  = await fetch('/driver/my-buses', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ line_user_id: lineUserId })
        });
        const data = await res.json();
        if (!data.success) {
            document.getElementById('status-box') && showStatus('error', '⚠️', 'ไม่พบข้อมูลรถ', data.message);
            return;
        }
        allBuses = data.buses;
        buildRouteDropdown();
    } catch(e) {
        showStatus('error', '❌', 'เชื่อมต่อไม่ได้', 'กรุณาลองใหม่');
    }
}

function buildRouteDropdown() {
    const routeSet = new Map();
    allBuses.forEach(b => { if (!routeSet.has(b.route_id)) routeSet.set(b.route_id, b.Route_Name); });

    const sel = document.getElementById('route-select');
    sel.innerHTML = '<option value="">— เลือกสายรถ —</option>';
    routeSet.forEach((name, id) => { sel.innerHTML += `<option value="${id}">${name}</option>`; });

    if (routeSet.size === 1) {
        const onlyId = routeSet.keys().next().value;
        sel.value = onlyId;
        filterBusesByRoute(onlyId);
    }
}

function filterBusesByRoute(routeId) {
    const sel  = document.getElementById('bus-select');
    const list = allBuses.filter(b => String(b.route_id) === String(routeId));
    sel.innerHTML = '<option value="">— เลือกทะเบียนรถ —</option>';
    list.forEach(b => {
        sel.innerHTML += `<option value="${b.bus_id}">${b.plate_no}${b.bus_no ? ' (' + b.bus_no + ')' : ''}</option>`;
    });
    if (list.length === 1) sel.value = list[0].bus_id;
}

function adjustCount(delta) {
    const el = document.getElementById('passenger_count');
    el.value = Math.max(0, (parseInt(el.value) || 0) + delta);
}

function showStatus(type, icon, msg, sub = '') {
    const box = document.getElementById('status-box');
    if (!box) return;
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

async function closeScanner() {
    document.getElementById('qr-modal').classList.add('hidden');
    if (html5QrCode) {
        try { await html5QrCode.stop(); } catch(_) {}
        html5QrCode = null;
    }
    resetBtn();
}

async function startScan() {
    const busId = document.getElementById('bus-select').value;
    const count = parseInt(document.getElementById('passenger_count').value);

    if (!busId)             { showStatus('error', '⚠️', 'กรุณาเลือกทะเบียนรถ'); return; }
    if (!count || count < 1){ showStatus('error', '⚠️', 'กรุณากรอกจำนวนพนักงาน'); return; }

    const btn = document.getElementById('scan-btn');
    btn.disabled = true;
    btn.innerHTML = `<svg class="w-5 h-5 spinner" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
    </svg> กำลังเปิดกล้อง...`;

    document.getElementById('qr-modal').classList.remove('hidden');
    html5QrCode = new Html5Qrcode('qr-reader');

    try {
        await html5QrCode.start(
            { facingMode: 'environment' },
            { fps: 10, qrbox: { width: 250, height: 250 } },
            async (token) => {
                await closeScanner();
                sendData(token, busId, count);
            },
            () => {}
        );
    } catch(e) {
        await closeScanner();
        showStatus('error', '❌', 'ไม่สามารถเปิดกล้องได้', e.message || 'กรุณาอนุญาตการใช้กล้อง');
    }
}

function sendData(token, busId, count) {
    showStatus('loading', '⏳', 'กำลังบันทึกข้อมูล...');

    fetch('/api/driver/checkin', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
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
</script>
@endpush
