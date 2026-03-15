@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')

@php
    $totalBuses      = \DB::table('emp_buses')->count();
    $pendingDrivers  = \DB::table('emp_buses')->whereNotNull('driver_name')->where('is_approved', false)->count();
    $approvedDrivers = \DB::table('emp_buses')->whereNotNull('driver_name')->where('is_approved', true)->count();
    $todayScans      = \DB::table('Bus_In_Out_Log')->whereDate('log_date_time', today())->count();
@endphp

{{-- Stats --}}
<div class="grid grid-cols-4 gap-6 mb-8">

    <a href="/admin/report"
        class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 hover:border-blue-300 hover:shadow-md transition-all group">
        <div class="w-11 h-11 rounded-xl bg-blue-50 group-hover:bg-blue-100 flex items-center justify-center mb-4 transition">
            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
        </div>
        <p class="text-3xl font-extrabold text-gray-800 mb-1">{{ $totalBuses }}</p>
        <p class="text-xs text-gray-400 font-semibold uppercase tracking-widest">ทะเบียนรถทั้งหมด</p>
    </a>

    <a href="/admin/drivers"
        class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 hover:border-orange-300 hover:shadow-md transition-all group">
        <div class="w-11 h-11 rounded-xl bg-orange-50 group-hover:bg-orange-100 flex items-center justify-center mb-4 transition">
            <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <p class="text-3xl font-extrabold text-orange-500 mb-1">{{ $pendingDrivers }}</p>
        <p class="text-xs text-gray-400 font-semibold uppercase tracking-widest">รอ Approve</p>
    </a>

    <a href="/admin/drivers"
        class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 hover:border-green-300 hover:shadow-md transition-all group">
        <div class="w-11 h-11 rounded-xl bg-green-50 group-hover:bg-green-100 flex items-center justify-center mb-4 transition">
            <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <p class="text-3xl font-extrabold text-green-600 mb-1">{{ $approvedDrivers }}</p>
        <p class="text-xs text-gray-400 font-semibold uppercase tracking-widest">คนขับอนุมัติแล้ว</p>
    </a>

    <a href="/admin/logs"
        class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 hover:border-purple-300 hover:shadow-md transition-all group">
        <div class="w-11 h-11 rounded-xl bg-purple-50 group-hover:bg-purple-100 flex items-center justify-center mb-4 transition">
            <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2
                       M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
        </div>
        <p class="text-3xl font-extrabold text-purple-600 mb-1">{{ $todayScans }}</p>
        <p class="text-xs text-gray-400 font-semibold uppercase tracking-widest">สแกนวันนี้</p>
    </a>

    <a href="/admin/buses"
        class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 hover:border-teal-300 hover:shadow-md transition-all group">
        <div class="w-11 h-11 rounded-xl bg-teal-50 group-hover:bg-teal-100 flex items-center justify-center mb-4 transition">
            <svg class="w-6 h-6 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
        </div>
        <p class="text-3xl font-extrabold text-teal-600 mb-1">—</p>
        <p class="text-xs text-gray-400 font-semibold uppercase tracking-widest">จัดการทะเบียนรถ</p>
    </a>

</div>

{{-- Alert --}}
@if($pendingDrivers > 0)
<div class="bg-orange-50 border border-orange-200 rounded-2xl p-5 flex items-center justify-between">
    <p class="text-orange-800 font-semibold text-sm">
        ⏳ มีคนขับรอการอนุมัติ <span class="font-black">{{ $pendingDrivers }}</span> คน
    </p>
    <a href="/admin/drivers"
        style="background-color:#ea580c; color:#fff;"
        class="px-4 py-2 rounded-xl text-sm font-bold hover:opacity-90 transition">
        อนุมัติเลย →
    </a>
</div>
@endif

@endsection