<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') — Kyokuyo Bus System</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Sarabun', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">

    {{-- Top Navbar (fixed) --}}
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white border-b border-gray-200 shadow-sm">
        <div class="px-8 h-14 flex items-center justify-between">

            {{-- Brand --}}
            <div class="flex items-center gap-3">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0"
                    style="background:#16a34a;">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                </div>
                <span class="font-bold text-gray-800 text-sm">Kyokuyo Bus System</span>
            </div>

            {{-- Nav Links --}}
            @php
                $pendingCount = \Illuminate\Support\Facades\DB::table('emp_buses')
                    ->whereNotNull('driver_name')->where('is_approved', false)->count();
                $navItems = [
                    ['href' => route('dashboard'),  'label' => 'Dashboard',        'match' => 'dashboard'],
                    ['href' => '/admin/report',      'label' => 'Report',           'match' => 'admin/report'],
                    ['href' => '/admin/drivers',     'label' => 'คนขับ / Approve',  'match' => 'admin/drivers', 'badge' => $pendingCount],
                    ['href' => '/admin/buses',       'label' => 'ทะเบียนรถ',        'match' => 'admin/buses'],
                    ['href' => route('admin.logs'), 'label' => 'Log QR', 'match' => 'admin/logs'],
                ];
            @endphp

            <div class="flex items-center gap-1">
                @foreach($navItems as $item)
                    @php $isActive = request()->is($item['match']); @endphp
                    <a href="{{ $item['href'] }}"
                        class="relative px-3 py-1.5 rounded-lg text-sm font-medium transition
                               {{ $isActive
                                   ? 'bg-green-50 text-green-700 font-semibold'
                                   : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100' }}">
                        {{ $item['label'] }}
                        @if(isset($item['badge']) && $item['badge'] > 0)
                            <span class="absolute -top-1 -right-1 bg-orange-500 text-white text-xs font-bold
                                         w-4 h-4 rounded-full flex items-center justify-center leading-none">
                                {{ $item['badge'] }}
                            </span>
                        @endif
                    </a>
                @endforeach
            </div>

            {{-- User --}}
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full bg-green-100 flex items-center justify-center
                                text-green-700 font-bold text-xs">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <span class="text-sm text-gray-600 font-medium">{{ Auth::user()->name }}</span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="text-xs text-red-500 hover:text-red-700 font-semibold
                               border border-red-200 hover:border-red-400 px-3 py-1.5 rounded-lg transition">
                        ออกจากระบบ
                    </button>
                </form>
            </div>

        </div>
    </nav>

    {{-- Page Content --}}
    <main class="pt-20 px-10 pb-8 w-full">

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-2 text-sm">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-lg mb-6 text-sm">
                <p class="font-semibold mb-1">กรุณาตรวจสอบข้อมูล</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Page Title --}}
        <div class="mb-6">
            <h1 class="text-xl font-bold text-gray-800">@yield('title', 'Dashboard')</h1>
            <p class="text-xs text-gray-400 mt-0.5">{{ now()->locale('th')->isoFormat('dddd D MMMM YYYY') }}</p>
        </div>

        @yield('content')

    </main>

</body>
</html>