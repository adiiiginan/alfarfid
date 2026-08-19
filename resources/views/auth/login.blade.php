<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AlfarFID</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-[#eef2fb] min-h-screen flex items-center justify-center relative overflow-hidden px-4 py-10">

    <!-- Decorative blobs -->
    <div class="absolute -top-28 -left-28 w-72 h-72 sm:w-96 sm:h-96 bg-blue-500/90 rounded-full pointer-events-none">
    </div>
    <div class="absolute -bottom-28 -right-28 w-72 h-72 sm:w-96 sm:h-96 bg-blue-500/90 rounded-full pointer-events-none">
    </div>
    <div
        class="absolute top-20 left-20 w-40 h-40 border border-white/50 rounded-full pointer-events-none hidden sm:block">
    </div>
    <div
        class="absolute bottom-20 right-20 w-40 h-40 border border-blue-300/50 rounded-full pointer-events-none hidden sm:block">
    </div>

    <!-- Decorative dot grids -->
    <div class="hidden lg:grid grid-cols-4 gap-3 absolute left-16 top-1/3 pointer-events-none">
        @for ($i = 0; $i < 12; $i++)
            <span class="w-1.5 h-1.5 rounded-full bg-blue-300/70"></span>
        @endfor
    </div>
    <div class="hidden lg:grid grid-cols-4 gap-3 absolute right-16 top-2/3 pointer-events-none">
        @for ($i = 0; $i < 12; $i++)
            <span class="w-1.5 h-1.5 rounded-full bg-blue-300/70"></span>
        @endfor
    </div>

    <div class="relative z-10 flex flex-col items-center w-full">
        <div
            class="bg-white shadow-2xl shadow-blue-900/10 rounded-2xl px-8 py-9 sm:px-10 sm:py-10 w-full max-w-[440px]">

            <!-- Logo -->
            <div class="flex justify-center mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" fill="none" class="w-14 h-14">
                    <path d="M9 7 H4 V12" stroke="#2563eb" stroke-width="2.5" stroke-linecap="round"
                        stroke-linejoin="round" />
                    <path d="M39 7 H44 V12" stroke="#2563eb" stroke-width="2.5" stroke-linecap="round"
                        stroke-linejoin="round" />
                    <path d="M9 41 H4 V36" stroke="#2563eb" stroke-width="2.5" stroke-linecap="round"
                        stroke-linejoin="round" />
                    <path d="M39 41 H44 V36" stroke="#2563eb" stroke-width="2.5" stroke-linecap="round"
                        stroke-linejoin="round" />
                    <path d="M14.5 27.5 a14 14 0 0 1 19 0" stroke="#2563eb" stroke-width="2.5" stroke-linecap="round" />
                    <path d="M19.5 22.5 a7.2 7.2 0 0 1 9 0" stroke="#2563eb" stroke-width="2.5"
                        stroke-linecap="round" />
                    <circle cx="24" cy="29" r="2.2" fill="#2563eb" />
                </svg>
            </div>

            <!-- Title -->
            <h1 class="text-center text-3xl font-extrabold tracking-tight mb-1">
                <span class="text-slate-900">Alfar</span><span class="text-blue-600">FID</span>
            </h1>
            <p class="text-center text-slate-500 text-sm mb-5">Sistem Tracking Garment &amp; Autoclave</p>

            <!-- Accent divider -->
            <div class="flex items-center justify-center gap-1 mb-6">
                <span class="h-px w-16 bg-slate-200"></span>
                <span class="h-0.5 w-10 bg-blue-600 rounded-full"></span>
                <span class="h-px w-16 bg-slate-200"></span>
            </div>

            <!-- Welcome -->
            <div class="text-center mb-6">
                <h2 class="text-lg font-bold text-slate-900">Selamat Datang Kembali!</h2>
                <p class="text-slate-500 text-sm mt-1">Silakan masuk untuk melanjutkan ke sistem</p>
            </div>

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-600 text-sm rounded-lg p-3 mb-5">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-800 mb-1.5">Email</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-blue-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="5" width="18" height="14" rx="2" />
                                <path d="M3 7l9 6 9-6" />
                            </svg>
                        </span>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required
                            autofocus
                            class="w-full h-12 border border-slate-200 rounded-lg pl-10 pr-3 text-sm text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Masukkan email Anda">
                    </div>
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-800 mb-1.5">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-blue-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="4" y="10" width="16" height="10" rx="2" />
                                <path d="M8 10V7a4 4 0 0 1 8 0v3" />
                            </svg>
                        </span>
                        <input type="password" name="password" id="password" required
                            class="w-full h-12 border border-slate-200 rounded-lg pl-10 pr-10 text-sm text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Masukkan password Anda">
                        <button type="button" onclick="togglePassword()" aria-label="Tampilkan password"
                            class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600">
                            <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z" />
                                <circle cx="12" cy="12" r="3" />
                                <path d="M3 3l18 18" id="eyeSlash" class="hidden" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember & Forgot -->
                <div class="flex items-center justify-between pt-1 pb-2">
                    <label class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer select-none">
                        <input type="checkbox" name="remember"
                            class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 w-4 h-4">
                        Ingat saya
                    </label>

                </div>

                <!-- Submit -->
                <button type="submit"
                    class="w-full h-12 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white text-sm font-semibold rounded-lg transition flex items-center justify-center gap-2 shadow-md shadow-blue-600/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
                        <path d="M10 17l5-5-5-5" />
                        <path d="M15 12H3" />
                    </svg>
                    Masuk
                </button>
            </form>



            <!-- Google Login -->


        </div>

        <!-- Footer -->
        <div class="flex items-center gap-2 mt-6 text-slate-500 text-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-500 flex-shrink-0" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                <path d="M9 12l2 2 4-4" />
            </svg>
            Data Anda aman bersama kami
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const slash = document.getElementById('eyeSlash');
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            slash.classList.toggle('hidden', !isPassword);
        }
    </script>

</body>

</html>
