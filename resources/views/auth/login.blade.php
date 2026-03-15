<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ — Bus Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&family=Kanit:wght@500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Sarabun', sans-serif; }
        .brand { font-family: 'Kanit', sans-serif; }
        .login-card {
            animation: slideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .bg-pattern {
            background-color: #0f172a;
            background-image:
                radial-gradient(circle at 20% 50%, rgba(22,163,74,0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(15,118,110,0.1) 0%, transparent 40%),
                linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        }
        .input-field {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.12);
            color: #f1f5f9;
            transition: all 0.2s;
        }
        .input-field::placeholder { color: rgba(255,255,255,0.3); }
        .input-field:focus {
            outline: none;
            border-color: #16a34a;
            background: rgba(22,163,74,0.08);
            box-shadow: 0 0 0 3px rgba(22,163,74,0.15);
        }
        .btn-login {
            background: linear-gradient(135deg, #16a34a, #15803d);
            transition: all 0.2s;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #15803d, #166534);
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(22,163,74,0.35);
        }
        .btn-login:active { transform: translateY(0); }
        .logo-ring {
            background: linear-gradient(135deg, #16a34a, #0d9488);
            padding: 3px;
            border-radius: 50%;
        }
        .logo-inner {
            background: #0f172a;
            border-radius: 50%;
            padding: 12px;
        }
    </style>
</head>
<body class="min-h-screen bg-pattern flex items-center justify-center px-4 py-12">

    <div class="login-card w-full max-w-md">

        {{-- Logo + Brand --}}
        <div class="text-center mb-8">
            <div class="inline-flex mb-4">
                <div class="logo-ring">
                    <div class="logo-inner">
                        <svg class="w-10 h-10 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                    </div>
                </div>
            </div>
            <h1 class="brand text-2xl font-700 text-white tracking-wide">
                KYOKUYO BUS SYSTEM
            </h1>
            <p class="text-slate-400 text-sm mt-1">Kyokuyo Industrial (Thailand)</p>
        </div>

        {{-- Card --}}
        <div class="rounded-2xl p-8" style="background: rgba(255,255,255,0.04); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.08);">

            <h2 class="text-lg font-semibold text-white mb-6">เข้าสู่ระบบ Admin</h2>

            {{-- Session Error --}}
            @if(session('status'))
                <div class="bg-green-900/40 border border-green-500/30 text-green-300 px-4 py-3 rounded-lg mb-4 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                {{-- Email --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-widest mb-2">
                        Email
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="input-field w-full px-4 py-3 rounded-xl text-sm"
                        placeholder="admin@kyokuyo-ind.co.th">
                    @error('email')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-widest mb-2">
                        Password
                    </label>
                    <div class="relative">
                        <input type="password" name="password" id="password" required
                            class="input-field w-full px-4 py-3 rounded-xl text-sm pr-12"
                            placeholder="••••••••">
                        <button type="button" onclick="togglePassword()"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white transition">
                            <svg id="eye-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7
                                       -1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember --}}
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember"
                            class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-green-500 focus:ring-green-500">
                        <span class="text-sm text-slate-400">จดจำการเข้าสู่ระบบ</span>
                    </label>
                    @if(Route::has('password.request'))
                        <a href="{{ route('password.request') }}"
                            class="text-sm text-green-400 hover:text-green-300 transition">
                            ลืมรหัสผ่าน?
                        </a>
                    @endif
                </div>

                {{-- Submit --}}
                <button type="submit" class="btn-login w-full py-3 rounded-xl text-white font-bold text-sm tracking-wide mt-2">
                    เข้าสู่ระบบ
                </button>
            </form>
        </div>

        <p class="text-center text-slate-600 text-xs mt-6">
            &copy; {{ date('Y') }} Kyokuyo Industrial (Thailand) Co., Ltd.
        </p>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    </script>
</body>
</html>