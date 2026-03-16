@extends('driver.layout')

@section('title', 'โปรไฟล์')

@section('content')
<div class="max-w-sm mx-auto">

    {{-- Profile header --}}
    <div class="px-4 pt-8 pb-6 text-center"
         style="background: linear-gradient(135deg, #16a34a, #0d9488);">
        <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center mx-auto mb-3">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
        </div>
        <h1 class="text-xl font-extrabold text-white">{{ $driver->driver_name }}</h1>
        <div class="mt-3">
            @if($driver->is_approved)
                <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-sm font-bold bg-white text-green-700">
                    ✅ อนุมัติแล้ว
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-sm font-bold bg-orange-100 text-orange-700">
                    ⏳ รอการอนุมัติ
                </span>
            @endif
        </div>
    </div>

    <div class="px-4 mt-4 space-y-4 pb-8">

        {{-- Registration info --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h2 class="text-sm font-bold text-gray-700 mb-3">ข้อมูลการลงทะเบียน</h2>
            <div class="space-y-2.5 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-400">ชื่อ</span>
                    <span class="font-semibold">{{ $driver->driver_name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">เบอร์โทร</span>
                    <span class="font-semibold">{{ $driver->phone }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">วันที่สมัคร</span>
                    <span class="font-semibold text-xs">
                        {{ $driver->created_at ? \Carbon\Carbon::parse($driver->created_at)->format('d/m/Y') : '-' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Assigned buses --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h2 class="text-sm font-bold text-gray-700 mb-3">รถที่ลงทะเบียนไว้</h2>
            @forelse($buses as $bus)
                <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                    <span class="font-semibold text-sm text-gray-800">
                        🚌 {{ $bus->plate_no }}{{ $bus->bus_no ? ' ('.$bus->bus_no.')' : '' }}
                    </span>
                    <span class="text-xs text-blue-600 font-medium">{{ $bus->Route_Name ?? '-' }}</span>
                </div>
            @empty
                <p class="text-gray-400 text-sm text-center py-2">ยังไม่มีรถ</p>
            @endforelse
        </div>

        {{-- Monthly summary --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h2 class="text-sm font-bold text-gray-700 mb-3">สรุปรายเดือน</h2>
            @forelse($monthly as $m)
                <div class="bg-gray-50 rounded-xl p-3 mb-2">
                    <div class="flex justify-between items-center mb-1">
                        <span class="font-bold text-gray-700 text-sm">{{ $m->month }}</span>
                        <span class="text-green-600 font-extrabold text-sm">{{ number_format($m->total_amount ?? 0) }} ฿</span>
                    </div>
                    <div class="flex gap-4 text-xs text-gray-500">
                        <span>🚌 {{ $m->total_trips }} เที่ยว</span>
                        <span>👥 {{ $m->total_passengers }} คน</span>
                    </div>
                </div>
            @empty
                <p class="text-gray-400 text-center py-4 text-sm">ยังไม่มีข้อมูล</p>
            @endforelse
        </div>

        {{-- Recent logs --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h2 class="text-sm font-bold text-gray-700 mb-3">
                ประวัติการสแกน QR
                <span class="text-xs text-gray-400 font-normal">(30 รายการล่าสุด)</span>
            </h2>
            @forelse($logs as $log)
                <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">
                            {{ ($log['actual_bus_type'] ?? '') === 'Van' ? '🚐' : '🚌' }}
                            {{ $log['factory_id'] ?? '-' }}
                            <span class="text-xs font-normal text-gray-400 ml-1">
                                {{ ($log['shift'] ?? '') === 'Day' ? '☀️' : '🌙' }}
                            </span>
                        </p>
                        <p class="text-xs text-blue-500 mt-0.5">
                            {{ $log['Route_Name'] ?? '-' }} · {{ $log['plate_no'] ?? '-' }}
                        </p>
                        <p class="text-xs text-gray-400">{{ $log['log_date_time'] ?? '-' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-gray-800">{{ number_format($log['applied_price'] ?? 0) }} ฿</p>
                        <p class="text-xs text-gray-500">{{ $log['passenger_count'] ?? 0 }} คน</p>
                    </div>
                </div>
            @empty
                <p class="text-gray-400 text-center py-4 text-sm">ยังไม่มีประวัติการสแกน</p>
            @endforelse
        </div>

    </div>
</div>
@endsection
