@extends('layouts.admin')

@section('title', 'Report สรุปยอด')

@section('content')

{{-- Page Header --}}
<div class="flex justify-between items-end mb-8">
    <div>
        <p class="text-green-600 font-bold uppercase tracking-widest text-xs mb-1">Logistics Management</p>
        <h1 class="text-3xl font-extrabold text-slate-900">สรุปค่าขนส่งพนักงาน</h1>
        <p class="text-slate-400 mt-1 text-sm">
            จัดเรียงตาม: <span class="font-semibold text-slate-600">บริษัทรถ › โรงงาน › สายรถ</span>
        </p>
    </div>
    <div class="flex gap-3">
        <button onclick="window.print()"
            class="px-4 py-2.5 bg-slate-100 text-slate-600 rounded-xl font-bold hover:bg-slate-200 transition text-sm flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2
                       m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5
                       a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            พิมพ์
        </button>
        <button onclick="exportTableToExcel('transport-table')"
            class="px-5 py-2.5 text-white rounded-xl font-bold transition text-sm flex items-center gap-2 shadow-lg shadow-emerald-100"
            style="background-color:#059669;">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586
                       a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Export Excel
        </button>
    </div>
</div>

{{-- Stats --}}
<div class="grid grid-cols-3 gap-6 mb-8">
    <div class="rounded-2xl p-6 text-white shadow-lg shadow-blue-100"
        style="background: linear-gradient(135deg, #2563eb, #1d4ed8);">
        <p class="text-blue-100 text-xs font-semibold uppercase tracking-widest">ยอดเงินสุทธิ</p>
        <p class="text-4xl font-black mt-2 tracking-tight">
            {{ number_format($reports->sum('total_amount'), 2) }}
            <span class="text-lg font-medium">฿</span>
        </p>
    </div>
    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
        <p class="text-slate-400 text-xs font-semibold uppercase tracking-widest">จำนวนเที่ยววิ่ง</p>
        <p class="text-4xl font-black text-slate-800 mt-2 tracking-tight">
            {{ number_format($reports->sum('total_trips')) }}
            <span class="text-lg font-medium text-slate-400">Tours</span>
        </p>
    </div>
    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
        <p class="text-slate-400 text-xs font-semibold uppercase tracking-widest">พนักงานรวม</p>
        <p class="text-4xl font-black text-slate-800 mt-2 tracking-tight">
            {{ number_format($reports->sum('total_passengers')) }}
            <span class="text-lg font-medium text-slate-400">Pax</span>
        </p>
    </div>
</div>

{{-- Filter Bar --}}
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-6 flex items-center gap-4">
    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894
               l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
    </svg>
    <input type="text" id="search-input" placeholder="ค้นหาบริษัท / สายรถ / โรงงาน..."
        class="flex-1 text-sm border-0 outline-none text-slate-700 placeholder-slate-400"
        oninput="filterTable()">
    <span id="result-count" class="text-xs text-slate-400 shrink-0">
        {{ $reports->count() }} รายการ
    </span>
</div>

{{-- Table --}}
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table id="transport-table" class="w-full text-left border-collapse text-sm">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200">
                    <th class="px-5 py-4 text-slate-500 font-bold uppercase text-xs">บริษัทรถ</th>
                    <th class="px-5 py-4 text-slate-500 font-bold uppercase text-xs">โรงงาน</th>
                    <th class="px-5 py-4 text-slate-500 font-bold uppercase text-xs">สายรถ</th>
                    <th class="px-5 py-4 text-slate-500 font-bold uppercase text-xs text-center">ประเภทรถ</th>
                    <th class="px-5 py-4 text-slate-500 font-bold uppercase text-xs text-center">เที่ยววิ่ง</th>
                    <th class="px-5 py-4 text-slate-500 font-bold uppercase text-xs text-center text-blue-600">พนักงาน</th>
                    <th class="px-5 py-4 text-slate-500 font-bold uppercase text-xs text-right text-emerald-600">ยอดเงิน (฿)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100" id="table-body">
                @php $lastVender = null; @endphp
                @foreach($reports as $item)

                    @if($lastVender !== $item->vender_id)
                        <tr class="bg-blue-50/60 data-row" data-search="{{ strtolower($item->vender_id) }}">
                            <td colspan="7" class="px-5 py-2.5 text-blue-800 font-black text-xs border-y border-blue-100 uppercase tracking-wide">
                                🏢 {{ $item->vender_id }}
                            </td>
                        </tr>
                        @php $lastVender = $item->vender_id; @endphp
                    @endif

                    <tr class="hover:bg-slate-50 transition data-row"
                        data-search="{{ strtolower($item->vender_id . ' ' . $item->factory_id . ' ' . $item->route_name) }}">
                        <td class="px-5 py-4 text-slate-400 font-mono text-xs">{{ $item->vender_id }}</td>
                        <td class="px-5 py-4">
                            <span class="px-2.5 py-1 text-xs font-bold rounded-lg
                                {{ $item->factory_id == 'fac1'
                                    ? 'bg-amber-100 text-amber-700'
                                    : 'bg-purple-100 text-purple-700' }}">
                                {{ $item->factory_id }}
                            </span>
                        </td>
                        <td class="px-5 py-4 font-semibold text-slate-700">{{ $item->route_name }}</td>
                        <td class="px-5 py-4 text-center">
                            <span class="font-semibold {{ $item->actual_bus_type == 'Van' ? 'text-indigo-500' : 'text-slate-600' }}">
                                {{ $item->actual_bus_type == 'Van' ? '🚐 รถตู้' : '🚌 รถบัส' }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-center font-mono text-slate-600">
                            {{ number_format($item->total_trips) }}
                        </td>
                        <td class="px-5 py-4 text-center font-mono font-bold text-blue-600">
                            {{ number_format($item->total_passengers) }}
                        </td>
                        <td class="px-5 py-4 text-right font-black text-slate-900 font-mono">
                            {{ number_format($item->total_amount, 2) }}
                        </td>
                    </tr>

                @endforeach
            </tbody>
            <tfoot class="bg-slate-900 text-white">
                <tr>
                    <td colspan="4" class="px-5 py-5 text-right text-xs uppercase tracking-widest text-slate-400 font-bold">
                        Grand Total
                    </td>
                    <td class="px-5 py-5 text-center border-l border-slate-800 font-bold">
                        {{ number_format($reports->sum('total_trips')) }}
                    </td>
                    <td class="px-5 py-5 text-center border-l border-slate-800 font-bold text-blue-300">
                        {{ number_format($reports->sum('total_passengers')) }}
                    </td>
                    <td class="px-5 py-5 text-right border-l border-slate-800 font-black text-emerald-400 text-xl">
                        {{ number_format($reports->sum('total_amount'), 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<p class="text-center text-slate-400 mt-8 text-xs">
    © {{ date('Y') }} Kyokuyo Industrial (Thailand) — Auto Generated Report
</p>

<script>
function filterTable() {
    const q = document.getElementById('search-input').value.toLowerCase();
    const rows = document.querySelectorAll('.data-row');
    let visible = 0;
    rows.forEach(row => {
        const match = row.dataset.search && row.dataset.search.includes(q);
        row.style.display = match ? '' : 'none';
        if (match) visible++;
    });
    document.getElementById('result-count').textContent = visible + ' รายการ';
}

function exportTableToExcel(tableID, filename = '') {
    var dataType = 'application/vnd.ms-excel';
    var tableSelect = document.getElementById(tableID);
    var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');
    filename = 'Transport_Summary_' + new Date().toLocaleDateString('th') + '.xls';
    var downloadLink = document.createElement('a');
    document.body.appendChild(downloadLink);
    downloadLink.href = 'data:' + dataType + ', \ufeff' + tableHTML;
    downloadLink.download = filename;
    downloadLink.click();
    document.body.removeChild(downloadLink);
}
</script>

@endsection