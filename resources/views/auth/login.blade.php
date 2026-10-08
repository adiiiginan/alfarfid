<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AlfarFID Smart Cleanroom</title>
    
    {{-- Tailwind CSS CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'Inter', 'system-ui', 'sans-serif'],
                        display: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', '"IBM Plex Mono"', 'monospace'],
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<body class="min-h-screen bg-slate-50 flex items-center justify-center relative overflow-hidden px-4 py-8 antialiased">

    {{-- Soft Light Pastel Orbs --}}
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-blue-200/40 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-indigo-200/40 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-slate-100/60 rounded-full blur-2xl pointer-events-none"></div>

    <div class="relative z-10 w-full max-w-md my-auto">
        
        {{-- Card Container (Bright White) --}}
        <div class="bg-white shadow-xl shadow-slate-200/70 rounded-3xl p-8 sm:p-10 border border-slate-200/80">

            {{-- Brand Header --}}
            <div class="flex flex-col items-center text-center mb-6">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-600/20 mb-3.5">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        <circle cx="12" cy="12" r="9" stroke-width="2"/>
                    </svg>
                </div>
                <h1 class="font-display text-2xl font-extrabold text-slate-900 tracking-tight">
                    Alfar<span class="text-indigo-600">FID</span>
                </h1>
                <p class="text-xs font-mono text-slate-500 mt-0.5">Centralized RFID Tracking &bull; Multi-Facility</p>
            </div>

            {{-- Error Alerts --}}
            @if ($errors->any())
                <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium space-y-1">
                    @foreach ($errors->all() as $error)
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 shrink-0 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" />
                                <path d="M12 8v4m0 4h.01" />
                            </svg>
                            <span>{{ $error }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Login Form --}}
            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-xs font-bold font-mono uppercase tracking-wider text-slate-700 mb-1.5">
                        Alamat Email
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3.5 flex items-center text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <rect x="3" y="5" width="18" height="14" rx="2" />
                                <path d="M3 7l9 6 9-6" />
                            </svg>
                        </span>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                            class="w-full h-11 border border-slate-200 rounded-xl pl-10 pr-3 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                            placeholder="nama@alfarfid.local">
                    </div>
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-xs font-bold font-mono uppercase tracking-wider text-slate-700 mb-1.5">
                        Password
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3.5 flex items-center text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <rect x="4" y="10" width="16" height="10" rx="2" />
                                <path d="M8 10V7a4 4 0 0 1 8 0v3" />
                            </svg>
                        </span>
                        <input type="password" name="password" id="password" required
                            class="w-full h-11 border border-slate-200 rounded-xl pl-10 pr-10 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                            placeholder="••••••••">
                        <button type="button" onclick="togglePassword()" aria-label="Tampilkan password"
                            class="absolute inset-y-0 right-3.5 flex items-center text-slate-400 hover:text-slate-600">
                            <svg id="eyeIcon" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z" />
                                <circle cx="12" cy="12" r="3" />
                                <path d="M3 3l18 18" id="eyeSlash" class="hidden" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Remember me --}}
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 text-xs text-slate-600 cursor-pointer select-none font-medium">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-3.5 h-3.5">
                        Ingat sesi saya
                    </label>
                </div>

                {{-- Submit Button --}}
                <button type="submit"
                    class="w-full h-11 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-sm font-semibold rounded-xl transition shadow-md shadow-indigo-600/20 flex items-center justify-center gap-2 font-display">
                    <span>Masuk ke Dashboard</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </form>

            {{-- Quick Demo Login Chips --}}
            <div class="mt-6 pt-5 border-t border-slate-100">
                <p class="text-[10px] uppercase font-mono font-bold text-slate-400 tracking-wider mb-2.5 text-center">
                    Akses Cepat Demo Akun
                </p>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" onclick="fillDemo('admin@alfarfid.local', 'admin')"
                        class="p-2 rounded-xl text-left bg-slate-50 hover:bg-indigo-50 border border-slate-200 hover:border-indigo-200 transition text-xs group">
                        <span class="block font-semibold text-slate-800 group-hover:text-indigo-600">👑 Super Admin</span>
                        <span class="block text-[10px] text-slate-400 font-mono">Pusat &bull; admin</span>
                    </button>
                    <button type="button" onclick="fillDemo('qa.supervisor@alfarfid.local', 'password123')"
                        class="p-2 rounded-xl text-left bg-slate-50 hover:bg-indigo-50 border border-slate-200 hover:border-indigo-200 transition text-xs group">
                        <span class="block font-semibold text-slate-800 group-hover:text-indigo-600">🔬 QA Supervisor</span>
                        <span class="block text-[10px] text-slate-400 font-mono">Pusat &bull; password123</span>
                    </button>
                    <button type="button" onclick="fillDemo('operator.bdg@alfarfid.local', 'password123')"
                        class="p-2 rounded-xl text-left bg-slate-50 hover:bg-blue-50 border border-slate-200 hover:border-blue-200 transition text-xs group">
                        <span class="block font-semibold text-slate-800 group-hover:text-blue-600">🏭 Op. Bandung</span>
                        <span class="block text-[10px] text-slate-400 font-mono">Plant BDG &bull; pass123</span>
                    </button>
                    <button type="button" onclick="fillDemo('operator.sby@alfarfid.local', 'password123')"
                        class="p-2 rounded-xl text-left bg-slate-50 hover:bg-emerald-50 border border-slate-200 hover:border-emerald-200 transition text-xs group">
                        <span class="block font-semibold text-slate-800 group-hover:text-emerald-600">🏭 Op. Surabaya</span>
                        <span class="block text-[10px] text-slate-400 font-mono">Plant SBY &bull; pass123</span>
                    </button>
                </div>
            </div>

        </div>

        {{-- Footer --}}
        <p class="text-center text-xs text-slate-400 font-mono mt-6">
            AlfarFID &bull; Keamanan enkripsi SSL/TLS &bull; ISO 14644 Compliant
        </p>

    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const slash = document.getElementById('eyeSlash');
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            slash.classList.toggle('hidden', !isPassword);
        }

        function fillDemo(email, pass) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = pass;
        }
    </script>

</body>

</html>
