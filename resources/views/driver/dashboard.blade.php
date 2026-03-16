@extends('driver.layout')

@section('title', 'หน้าหลัก')

@section('content')
<div class="max-w-sm mx-auto px-4 pt-8 pb-4">

    {{-- Header card --}}
    <div class="rounded-2xl text-white p-5 mb-5"
         style="background: linear-gradient(135deg, #16a34a, #0d9488);">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div>
                <p class="text-white/70 text-xs">สวัสดี</p>
                <p class="font-extrabold text-lg leading-tight">{{ $driver->driver_name }}</p>
            </div>
        </div>

        @if($driver->is_approved)
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-white text-green-700">
                ✅ อนุมัติแล้ว
            </span>
        @else
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-orange-100 text-orange-700">
                ⏳ รอการอนุมัติ
            </span>
        @endif
    </div>

    {{-- Stats row --}}
    <div class="grid grid-cols-2 gap-3 mb-5">
        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm text-center">
            <p class="text-3xl font-extrabold text-green-600">{{ $busCount }}</p>
            <p class="text-xs text-gray-500 mt-1">รถที่ลงทะเบียน</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm text-center">
            <p class="text-3xl font-extrabold text-blue-600">{{ $monthlyTotal }}</p>
            <p class="text-xs text-gray-500 mt-1">เที่ยววิ่งเดือนนี้</p>
        </div>
    </div>

    {{-- Quick action --}}
    @if($driver->is_approved)
    <div class="mb-5">
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">ดำเนินการ</p>
        <a href="{{ route('driver.checkin') }}"
           class="flex items-center gap-3 p-4 rounded-2xl text-white font-bold"
           style="background:#16a34a;">
            <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8H3m2 4H3m18-4h-2M5 20H3"/>
            </svg>
            <div>
                <p class="text-base">บันทึกเที่ยววิ่ง</p>
                <p class="text-xs font-normal text-white/70">สแกน QR Code จากโรงงาน</p>
            </div>
        </a>
    </div>
    @else
    <div class="bg-orange-50 border border-orange-200 rounded-2xl p-4 mb-5">
        <p class="text-sm font-bold text-orange-700 mb-1">รอการอนุมัติจาก Admin</p>
        <p class="text-xs text-orange-500">ระบบจะอนุมัติบัญชีของคุณเร็วๆ นี้</p>
    </div>
    @endif

    {{-- Recent trips --}}
    @if(count($recentLogs) > 0)
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <div class="flex justify-between items-center mb-3">
            <h2 class="text-sm font-bold text-gray-700">เที่ยววิ่งล่าสุด</h2>
            <a href="{{ route('driver.profile') }}" class="text-xs text-green-600 font-semibold">ดูทั้งหมด →</a>
        </div>
        @foreach($recentLogs as $log)
        <div class="flex items-center justify-between py-2.5 border-b border-gray-100 last:border-0">
            <div>
                <p class="text-sm font-semibold text-gray-800">
                    {{ ($log['bus_type'] ?? '') === 'Van' ? '🚐' : '🚌' }}
                    {{ $log['factory_id'] ?? '-' }}
                    <span class="text-xs font-normal text-gray-400">
                        {{ ($log['shift'] ?? '') === 'Day' ? '☀️' : '🌙' }}
                    </span>
                </p>
                <p class="text-xs text-gray-400">{{ $log['log_date_time'] ?? '-' }}</p>
            </div>
            <p class="text-sm font-bold text-gray-800">{{ number_format($log['applied_price'] ?? 0) }} ฿</p>
        </div>
        @endforeach
    </div>
    @endif

</div>
@endsection
