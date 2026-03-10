@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-6 py-8">

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">จัดการทะเบียนรถ</h2>
        <a href="{{ route('admin.drivers') }}" class="text-blue-600 hover:underline text-sm">← คนขับ</a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
            ✅ {{ session('success') }}
        </div>
    @endif

    @if($errors->has('delete'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
            ⚠️ {{ $errors->first('delete') }}
        </div>
    @endif

    {{-- ฟอร์มเพิ่มรถใหม่ --}}
    <div class="bg-white rounded-xl shadow p-6 mb-8">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">➕ เพิ่มทะเบียนรถใหม่</h3>
        <form action="{{ route('admin.buses.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">สายรถ *</label>
                    <select name="route_id" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400">
                        <option value="">-- เลือกสาย --</option>
                        @foreach($routes as $route)
                            <option value="{{ $route->Route_ID }}" {{ old('route_id') == $route->Route_ID ? 'selected' : '' }}>
                                {{ $route->Route_Name }}
                            </option>
                        @endforeach
                    </select>
                    @error('route_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">ทะเบียนรถ *</label>
                    <input type="text" name="plate_no" value="{{ old('plate_no') }}"
                        placeholder="เช่น กข-1234" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 uppercase">
                    @error('plate_no') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">หมายเลขรถ</label>
                    <input type="text" name="bus_no" value="{{ old('bus_no') }}"
                        placeholder="เช่น B01"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">ความจุ (คน)</label>
                    <input type="number" name="capacity" value="{{ old('capacity') }}"
                        placeholder="40" min="1"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">บริษัทรถ</label>
                    <input type="text" name="vender_id" value="{{ old('vender_id') }}"
                        placeholder="ชื่อบริษัท"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400">
                </div>

            </div>
            <div class="mt-4">
                <button type="submit"
                    style="background-color: #1d4ed8; color: #ffffff;"
                    class="px-6 py-2 rounded-lg font-bold text-sm shadow hover:opacity-90 transition">
                    เพิ่มรถ
                </button>
            </div>
        </form>
    </div>

    {{-- ตารางรายการรถ --}}
    <div class="bg-white shadow rounded-xl overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">สายรถ</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">ทะเบียน</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">หมายเลขรถ</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">ความจุ</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">บริษัท</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($buses as $bus)
                <tr class="hover:bg-gray-50 border-b border-gray-200" id="row-{{ $bus->bus_id }}">

                    {{-- View mode --}}
                    <td class="px-4 py-3 view-{{ $bus->bus_id }}">
                        <span class="font-semibold text-blue-700">{{ $bus->Route_Name ?? '-' }}</span>
                    </td>
                    <td class="px-4 py-3 view-{{ $bus->bus_id }}">
                        <span class="font-mono font-bold">{{ $bus->plate_no }}</span>
                    </td>
                    <td class="px-4 py-3 view-{{ $bus->bus_id }}">{{ $bus->bus_no ?? '-' }}</td>
                    <td class="px-4 py-3 view-{{ $bus->bus_id }}">{{ $bus->capacity ?? '-' }}</td>
                    <td class="px-4 py-3 view-{{ $bus->bus_id }}">{{ $bus->vender_id ?? '-' }}</td>

                    {{-- Edit mode (hidden) --}}
                    <td colspan="5" class="px-4 py-3 edit-{{ $bus->bus_id }} hidden">
                        <form action="{{ route('admin.buses.update', $bus->bus_id) }}" method="POST">
                            @csrf @method('PUT')
                            <div class="grid grid-cols-5 gap-2">
                                <select name="route_id" required
                                    class="border border-gray-300 rounded px-2 py-1.5 text-sm">
                                    @foreach($routes as $route)
                                        <option value="{{ $route->Route_ID }}"
                                            {{ $bus->route_id == $route->Route_ID ? 'selected' : '' }}>
                                            {{ $route->Route_Name }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="text" name="plate_no" value="{{ $bus->plate_no }}"
                                    class="border border-gray-300 rounded px-2 py-1.5 text-sm uppercase" required>
                                <input type="text" name="bus_no" value="{{ $bus->bus_no }}"
                                    class="border border-gray-300 rounded px-2 py-1.5 text-sm"
                                    placeholder="หมายเลขรถ">
                                <input type="number" name="capacity" value="{{ $bus->capacity }}"
                                    class="border border-gray-300 rounded px-2 py-1.5 text-sm"
                                    placeholder="ความจุ" min="1">
                                <input type="text" name="vender_id" value="{{ $bus->vender_id }}"
                                    class="border border-gray-300 rounded px-2 py-1.5 text-sm"
                                    placeholder="บริษัท">
                            </div>
                            <div class="mt-2 flex gap-2">
                                <button type="submit"
                                    style="background-color: #16a34a; color:#fff;"
                                    class="px-4 py-1.5 rounded text-xs font-bold hover:opacity-90">บันทึก</button>
                                <button type="button" onclick="toggleEdit({{ $bus->bus_id }}, false)"
                                    class="px-4 py-1.5 rounded text-xs font-bold bg-gray-200 text-gray-700 hover:bg-gray-300">ยกเลิก</button>
                            </div>
                        </form>
                    </td>

                    {{-- Actions --}}
                    <td class="px-4 py-3 text-center view-{{ $bus->bus_id }}">
                        <button onclick="toggleEdit({{ $bus->bus_id }}, true)"
                            class="bg-yellow-400 hover:bg-yellow-500 text-white text-xs font-bold px-3 py-1.5 rounded mr-1 transition">
                            แก้ไข
                        </button>
                        <form action="{{ route('admin.buses.destroy', $bus->bus_id) }}" method="POST"
                            class="inline"
                            onsubmit="return confirm('ลบทะเบียน {{ $bus->plate_no }} ?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                class="bg-red-500 hover:bg-red-600 text-white text-xs font-bold px-3 py-1.5 rounded transition">
                                ลบ
                            </button>
                        </form>
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-gray-400">
                        ยังไม่มีข้อมูลรถ
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

<script>
function toggleEdit(id, show) {
    document.querySelectorAll(`.view-${id}`).forEach(el => el.classList.toggle('hidden', show));
    document.querySelectorAll(`.edit-${id}`).forEach(el => el.classList.toggle('hidden', !show));
}
</script>
@endsection