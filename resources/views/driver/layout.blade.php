<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#16a34a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="คนขับรถ">

    <link rel="manifest" href="/manifest-driver.json">
    <link rel="apple-touch-icon" href="/favicon.ico">

    <title>@yield('title', 'ระบบคนขับรถ') — Kyokuyo</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Sarabun', sans-serif;
            background: #f0fdf4;
            padding-bottom: env(safe-area-inset-bottom, 0px);
        }
        .page-content {
            padding-bottom: calc(64px + env(safe-area-inset-bottom, 0px));
        }
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: calc(64px + env(safe-area-inset-bottom, 0px));
            padding-bottom: env(safe-area-inset-bottom, 0px);
            background: #fff;
            border-top: 1px solid #e5e7eb;
            display: flex;
            align-items: flex-start;
            z-index: 100;
            box-shadow: 0 -2px 12px rgba(0,0,0,0.06);
        }
        .nav-item {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 64px;
            gap: 2px;
            color: #9ca3af;
            text-decoration: none;
            font-size: 11px;
            font-weight: 600;
            transition: color 0.15s;
            -webkit-tap-highlight-color: transparent;
        }
        .nav-item.active { color: #16a34a; }
        .nav-item svg { width: 24px; height: 24px; }
    </style>

    @stack('head')
</head>
<body>

<div class="page-content">
    @yield('content')
</div>

<nav class="bottom-nav">
    <a href="{{ route('driver.dashboard') }}"
       class="nav-item {{ Request::routeIs('driver.dashboard') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
        หน้าหลัก
    </a>

    <a href="{{ route('driver.checkin') }}"
       class="nav-item {{ Request::routeIs('driver.checkin') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8H3m2 4H3m18-4h-2M5 20H3"/>
        </svg>
        สแกน QR
    </a>

    <a href="{{ route('driver.profile') }}"
       class="nav-item {{ Request::routeIs('driver.profile') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        โปรไฟล์
    </a>
</nav>

@stack('scripts')
</body>
</html>
