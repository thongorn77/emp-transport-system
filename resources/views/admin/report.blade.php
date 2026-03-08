<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transport Logistics Report</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; background-color: #f8fafc; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body class="antialiased text-slate-800">

    <div class="max-w-7xl mx-auto px-4 py-10">
        
        <div class="flex flex-col md:flex-row justify-between items-end mb-8 bg-white p-8 rounded-3xl shadow-sm border border-slate-200">
            <div>
                <span class="text-blue-600 font-bold uppercase tracking-widest text-sm">Logistics Management</span>
                <h1 class="text-4xl font-extrabold text-slate-900 mt-1">สรุปค่าขนส่งพนักงาน</h1>
                <p class="text-slate-500 mt-2">จัดเรียงตาม: <span class="font-semibold text-slate-700">บริษัทรถ > โรงงาน > สายรถ</span></p>
            </div>
            <div class="flex gap-4 mt-6 md:mt-0 no-print">
                <button onclick="window.print()" class="px-5 py-2.5 bg-slate-100 text-slate-600 rounded-xl font-bold hover:bg-slate-200 transition flex items-center gap-2">
                    พิมพ์รายงาน
                </button>
                <button onclick="exportTableToExcel('transport-table')" class="px-6 py-2.5 bg-emerald-600 text-white rounded-xl font-bold hover:bg-emerald-700 shadow-lg shadow-emerald-200 transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Export to Excel
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 no-print">
            <div class="bg-gradient-to-br from-blue-600 to-blue-700 p-7 rounded-3xl shadow-xl shadow-blue-100 text-white">
                <p class="text-blue-100 text-sm font-semibold uppercase tracking-wider">ยอดเงินสุทธิทั้งหมด</p>
                <h2 class="text-4xl font-black mt-2 tracking-tight">{{ number_format($reports->sum('total_amount'), 2) }} <span class="text-xl font-medium">฿</span></h2>
            </div>
            <div class="bg-white p-7 rounded-3xl shadow-sm border border-slate-200">
                <p class="text-slate-400 text-sm font-semibold uppercase tracking-wider">จำนวนเที่ยววิ่ง</p>
                <h2 class="text-4xl font-black text-slate-800 mt-2 tracking-tight">{{ number_format($reports->sum('total_trips')) }} <span class="text-xl font-medium text-slate-400">Tours</span></h2>
            </div>
            <div class="bg-white p-7 rounded-3xl shadow-sm border border-slate-200">
                <p class="text-slate-400 text-sm font-semibold uppercase tracking-wider">จำนวนพนักงานรวม</p>
                <h2 class="text-4xl font-black text-slate-800 mt-2 tracking-tight">{{ number_format($reports->sum('total_passengers')) }} <span class="text-xl font-medium text-slate-400">Pax</span></h2>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table id="transport-table" class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="p-5 text-slate-500 font-bold uppercase text-xs">บริษัทรถ (Vender)</th>
                            <th class="p-5 text-slate-500 font-bold uppercase text-xs">โรงงาน</th>
                            <th class="p-5 text-slate-500 font-bold uppercase text-xs">สายรถ (Route)</th>
                            <th class="p-5 text-slate-500 font-bold uppercase text-xs text-center">ประเภทรถ</th>
                            <th class="p-5 text-slate-500 font-bold uppercase text-xs text-center">จำนวนเที่ยว</th>
                            <th class="p-5 text-slate-500 font-bold uppercase text-xs text-center text-blue-600">พนักงาน</th>
                            <th class="p-5 text-slate-500 font-bold uppercase text-xs text-right text-emerald-600">ยอดเงินรวม</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @php $lastVender = null; @endphp
                        @foreach($reports as $item)
                            {{-- Section: Vender Header --}}
                            @if($lastVender !== $item->vender_id)
                                <tr class="bg-blue-50/40">
                                    <td colspan="7" class="px-5 py-3 text-blue-800 font-black text-sm border-y border-blue-100">
                                        🏢 VENDER ID: {{ $item->vender_id }} 
                                        <span class="ml-2 font-normal text-blue-400">| ข้อมูลแยกตามสายรถ</span>
                                    </td>
                                </tr>
                                @php $lastVender = $item->vender_id; @endphp
                            @endif

                            <tr class="hover:bg-slate-50/80 transition group">
                                <td class="p-5 text-slate-300 font-mono text-xs italic group-hover:text-slate-500">#{{ $item->vender_id }}</td>
                                <td class="p-5">
                                    <span class="px-3 py-1 text-[10px] font-black uppercase rounded-lg {{ $item->factory_id == 'fac1' ? 'bg-amber-100 text-amber-700' : 'bg-purple-100 text-purple-700' }}">
                                        {{ $item->factory_id }}
                                    </span>
                                </td>
                                <td class="p-5 font-bold text-slate-700">{{ $item->route_name }}</td>
                                <td class="p-5 text-center">
                                    <span class="text-sm font-semibold {{ $item->actual_bus_type == 'Van' ? 'text-indigo-500' : 'text-slate-600' }}">
                                        {{ $item->actual_bus_type == 'Van' ? '🚐 รถตู้' : '🚌 รถบัส' }}
                                    </span>
                                </td>
                                <td class="p-5 text-center font-mono text-slate-600">{{ number_format($item->total_trips) }}</td>
                                <td class="p-5 text-center font-mono font-bold text-blue-600">{{ number_format($item->total_passengers) }}</td>
                                <td class="p-5 text-right font-black text-slate-900 font-mono">
                                    {{ number_format($item->total_amount, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-900 text-white font-bold">
                        <tr>
                            <td colspan="4" class="p-6 text-right text-sm uppercase tracking-widest text-slate-400">Grand Total Amount</td>
                            <td class="p-6 text-center border-l border-slate-800">{{ number_format($reports->sum('total_trips')) }}</td>
                            <td class="p-6 text-center border-l border-slate-800 text-blue-300">{{ number_format($reports->sum('total_passengers')) }}</td>
                            <td class="p-6 text-right border-l border-slate-800 text-emerald-400 text-2xl font-black">
                                {{ number_format($reports->sum('total_amount'), 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <p class="text-center text-slate-400 mt-10 text-sm no-print">© 2026 Fleet Management System • Automation Report Generated</p>
    </div>

    <script>
        function exportTableToExcel(tableID, filename = '') {
            var downloadLink;
            var dataType = 'application/vnd.ms-excel';
            var tableSelect = document.getElementById(tableID);
            var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');
            filename = filename ? filename + '.xls' : 'Transport_Summary_' + new Date().toLocaleDateString() + '.xls';
            downloadLink = document.createElement("a");
            document.body.appendChild(downloadLink);
            if (navigator.msSaveOrOpenBlob) {
                var blob = new Blob(['\ufeff', tableHTML], { type: dataType });
                navigator.msSaveOrOpenBlob(blob, filename);
            } else {
                downloadLink.href = 'data:' + dataType + ', \ufeff' + tableHTML;
                downloadLink.download = filename;
                downloadLink.click();
            }
        }
    </script>
</body>
</html>