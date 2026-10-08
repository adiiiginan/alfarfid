<aside id="sidebar"
    class="sidebar sidebar-ocean fixed lg:sticky top-0 left-0 z-40 w-64 h-screen max-h-screen flex flex-col transition-transform duration-200 border-r border-blue-900/30 shadow-lg shadow-blue-900/10 shrink-0 select-none">

    {{-- Brand Header --}}
    <div class="flex items-center gap-3 px-5 py-4 border-b border-white/15 shrink-0">
        <div class="w-9 h-9 rounded-xl bg-white text-blue-700 flex items-center justify-center shadow-md shadow-blue-900/20 shrink-0">
            <svg class="w-5 h-5 text-blue-700" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                <circle cx="12" cy="12" r="9" stroke-width="2"/>
            </svg>
        </div>
        <div class="min-w-0">
            <div class="flex items-center gap-1.5">
                <span class="font-display font-bold text-white text-base tracking-tight">Alfar<span class="text-cyan-300">FID</span></span>
                <span class="px-1.5 py-0.5 rounded text-[9px] font-mono font-bold bg-cyan-400/20 text-cyan-200 border border-cyan-300/30">OP</span>
            </div>
            <p class="text-[11px] font-mono text-blue-100 truncate">Operator Workstation</p>
        </div>
    </div>

    {{-- Navigation Links (Scrollable Area) --}}
    <nav class="flex-1 px-3 py-4 space-y-5 overflow-y-auto min-h-0">

        {{-- ================= OPERASIONAL ================= --}}
        <div>
            <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-blue-200/90 font-mono mb-1.5">Operasional Sterilisasi</p>
            <div class="space-y-1">

                {{-- Dashboard / Scan Events --}}
                <a href="{{ route('operator.dashboard') }}"
                    class="nav-link flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium {{ request()->routeIs('operator.dashboard') ? 'active' : '' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span>Live Scan Events</span>
                </a>
            </div>
        </div>

    </nav>

    {{-- Bottom Multi-Facility Status Card (Translucent Blue) --}}
    <div class="p-3.5 border-t border-white/15 shrink-0 mt-auto bg-black/15">
        <div class="rounded-xl bg-white/10 p-2.5 border border-white/15 text-xs backdrop-blur-xs">
            <p class="text-[10px] uppercase font-mono font-bold text-blue-200 mb-1.5 tracking-wider">Multi-Plant Sync</p>
            <div class="space-y-1 font-mono text-[11px]">
                <div class="flex items-center justify-between text-white">
                    <span class="flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        Pabrik Bandung
                    </span>
                    <span class="text-[10px] text-emerald-300 font-semibold">Online</span>
                </div>
                <div class="flex items-center justify-between text-white">
                    <span class="flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        Pabrik Surabaya
                    </span>
                    <span class="text-[10px] text-emerald-300 font-semibold">Online</span>
                </div>
            </div>
        </div>

        {{-- Quick User Info & Logout --}}
        <div class="mt-2.5 pt-2.5 border-t border-white/10 flex items-center justify-between">
            <div class="flex items-center gap-2 min-w-0">
                <div class="w-7 h-7 rounded-lg bg-cyan-400/20 text-cyan-200 border border-cyan-300/30 flex items-center justify-center font-bold font-mono text-xs shrink-0">
                    {{ strtoupper(substr(auth()->user()->full_name ?? (auth()->user()->name ?? 'OP'), 0, 2)) }}
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-bold text-white truncate leading-tight">{{ auth()->user()->full_name ?? (auth()->user()->name ?? 'Operator') }}</p>
                    <p class="text-[10px] text-cyan-200/80 font-mono truncate">Operator</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Keluar"
                    class="p-1.5 rounded-lg text-blue-200 hover:text-white hover:bg-white/10 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        </div>
    </div>

</aside>
