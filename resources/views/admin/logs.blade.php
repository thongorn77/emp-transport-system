@extends('layouts.admin')

@section('title', 'Log การสแกน QR')

@section('content')

{{-- Filter --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-6">
    <form method="GET" action="/admin/logs" class="flex items-end gap-4 flex-wrap">

        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">ตั้งแต่วันที่</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-400">
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">ถึงวันที่</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-400">
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">สายรถ</label>
            <select name="route_id"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-400 bg-white">
                <option value="">-- ทั้งหมด --</option>
                @foreach($routes as $route)
                    <option value="{{ $route->Route_ID }}"
                        {{ request('route_id') == $route->Route_ID ? 'selected' : '' }}>
                        {{ $route->Route_Name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">โรงงาน</label>
            <select name="factory_id"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-400 bg-white">
                <option value="">-- ทั้งหมด --</option>
                <option value="fac1" {{ request('factory_id') == 'fac1' ? 'selected' : '' }}>โรงงาน 1</option>
                <option value="fac2" {{ request('factory_id') == 'fac2' ? 'selected' : '' }}>โรงงาน 2</option>
            </select>
        </div>

        <div class="flex gap-2">
            <button type="submit"
                style="background-color:#16a34a; color:#fff;"
                class="px-4 py-2 rounded-lg text-sm font-bold hover:opacity-90 transition">
                ค้นหา
            </button>
            <a href="/admin/logs"
                class="px-4 py-2 rounded-lg text-sm font-bold bg-gray-100 text-gray-600 hover:bg-gray-200 transition">
                รีเซ็ต
            </a>
        </div>

    </form>
</div>

{{-- Summary --}}
<div class="grid gap-4 mb-6" style="display:grid; grid-template-columns: repeat(3, 1fr); gap:1rem;">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <p class="text-xs text-gray-400 uppercase tracking-widest font-semibold mb-2">รายการทั้งหมด</p>
        <p class="text-3xl font-extrabold text-gray-800">{{ number_format($logs->total()) }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <p class="text-xs text-gray-400 uppercase tracking-widest font-semibold mb-2">พนักงานรวม</p>
        <p class="text-3xl font-extrabold text-blue-600">{{ number_format($logs->sum('passenger_count')) }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <p class="text-xs text-gray-400 uppercase tracking-widest font-semibold mb-2">ยอดเงินรวม</p>
        <p class="text-3xl font-extrabold text-green-600">{{ number_format($logs->sum('applied_price'), 2) }} ฿</p>
    </div>
</div>

{{-- Table --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wide">วันที่/เวลา</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wide">คนขับ</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wide">ทะเบียน</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wide">สายรถ</th>
                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wide">โรงงาน</th>
                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wide">กะ</th>
                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wide">ประเภทรถ</th>
                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wide">พนักงาน</th>
                    <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wide">ยอดเงิน</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($logs as $log)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 text-gray-600 whitespace-nowrap font-mono text-xs">
                        {{ \Carbon\Carbon::parse($log->log_date_time)->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-4 py-3 font-semibold text-gray-800">
                        {{ $log->driver_name ?? '-' }}
                    </td>
                    <td class="px-4 py-3 font-mono text-gray-600">
                        {{ $log->plate_no ?? '-' }}
                    </td>
                    <td class="px-4 py-3 text-gray-700">
                        {{ $log->Route_Name ?? '-' }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-1 rounded-lg text-xs font-bold
                            {{ $log->factory_id == 'fac1' ? 'bg-amber-100 text-amber-700' : 'bg-purple-100 text-purple-700' }}">
                            {{ $log->factory_id }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-1 rounded-lg text-xs font-semibold
                            {{ $log->shift == 'Day' ? 'bg-yellow-100 text-yellow-700' : 'bg-indigo-100 text-indigo-700' }}">
                            {{ $log->shift == 'Day' ? '☀️ Day' : '🌙 Night' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center text-sm">
                        {{ $log->actual_bus_type == 'Van' ? '🚐 รถตู้' : '🚌 รถบัส' }}
                    </td>
                    <td class="px-4 py-3 text-center font-bold text-blue-600">
                        {{ number_format($log->passenger_count) }}
                    </td>
                    <td class="px-4 py-3 text-right font-bold text-gray-800 font-mono">
                        {{ number_format($log->applied_price, 2) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-4 py-12 text-center text-gray-400">
                        ไม่พบข้อมูล Log
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($logs->hasPages())
    <div class="px-4 py-4 border-t border-gray-100">
        {{ $logs->withQueryString()->links() }}
    </div>
    @endif

</div>

@endsection