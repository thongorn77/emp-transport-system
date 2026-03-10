@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">จัดการข้อมูลคนขับรถ</h2>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-2">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow p-4 text-center">
            <p class="text-3xl font-extrabold text-blue-600">{{ $drivers->count() }}</p>
            <p class="text-sm text-gray-500 mt-1">ทั้งหมด</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4 text-center">
            <p class="text-3xl font-extrabold text-orange-500">
                {{ $drivers->where('is_approved', false)->count() }}
            </p>
            <p class="text-sm text-gray-500 mt-1">รอ Approve</p>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white shadow-md rounded-xl overflow-hidden">
        <table class="min-w-full leading-normal">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        ชื่อคนขับ / เบอร์โทร
                    </th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        รถที่ลงทะเบียน
                    </th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        สถานะ
                    </th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        จัดการ
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse($drivers as $driver)
                @php
                    $buses = $driverBuses[$driver->line_user_id] ?? collect();
                @endphp
                <tr class="hover:bg-gray-50 transition">

                    <td class="px-5 py-4 border-b border-gray-200 text-sm">
                        <p class="text-gray-900 font-bold">{{ $driver->driver_name }}</p>
                        <p class="text-gray-500 text-xs mt-0.5">{{ $driver->phone }}</p>
                        <p class="text-gray-300 text-xs font-mono mt-0.5">
                            {{ Str::limit($driver->line_user_id, 20) }}
                        </p>
                    </td>

                    <td class="px-5 py-4 border-b border-gray-200 text-sm">
                        @forelse($buses as $bus)
                            <div class="text-xs mb-1">
                                <span class="font-semibold text-gray-700">{{ $bus->plate_no }}</span>
                                <span class="text-blue-500 ml-1">{{ $bus->Route_Name }}</span>
                            </div>
                        @empty
                            <span class="text-gray-400 text-xs">ไม่มีรถ</span>
                        @endforelse
                    </td>

                    <td class="px-5 py-4 border-b border-gray-200 text-sm">
                        @if($driver->is_approved)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                ✅ อนุมัติแล้ว
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-800">
                                ⏳ รอตรวจสอบ
                            </span>
                        @endif
                        <p class="text-gray-400 text-xs mt-1">{{ \Carbon\Carbon::parse($driver->created_at)->format('d/m/Y') }}</p>
                    </td>

                    <td class="px-5 py-4 border-b border-gray-200 text-center">
                        @if(!$driver->is_approved)
                            <form action="{{ url('/admin/approve/' . $driver->id) }}" method="POST"
                                onsubmit="return confirm('ยืนยันการอนุมัติ {{ $driver->driver_name }}?')">
                                @csrf
                                <button type="submit"
                                    style="background-color: #2563eb; color: #ffffff;"
                                    class="font-bold py-1.5 px-4 rounded-lg text-xs shadow hover:opacity-90 active:scale-95 transition">
                                    Approve
                                </button>
                            </form>
                        @else
                            <span class="text-gray-400 text-xs italic">Confirmed</span>
                        @endif
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-5 py-10 text-center text-gray-400 text-sm">
                        ยังไม่มีข้อมูลคนขับที่ลงทะเบียน
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6 text-right">
        <a href="{{ url('/admin/report') }}" class="text-blue-600 hover:underline text-sm">
            ← กลับไปหน้า Dashboard
        </a>
    </div>

</div>
@endsection
