<header
    class="bg-white/85 backdrop-blur-md border-b border-blue-100/90 px-4 sm:px-6 lg:px-8 py-3.5 flex items-center justify-between sticky top-0 z-20 transition-all shadow-xs">
    
    {{-- Left: Mobile Toggle & Page Title --}}
    <div class="flex items-center gap-3">
        <button id="sidebarToggle" class="lg:hidden p-2 -ml-1 text-slate-600 hover:text-blue-700 rounded-lg hover:bg-blue-50 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
        <div>
            <h2 class="font-display font-bold text-lg text-slate-900 leading-tight">@yield('page-title', 'Live Scan Events')</h2>
            <div class="flex items-center gap-2 text-xs text-slate-500 font-mono mt-0.5">
                <span>{{ now()->translatedFormat('l, d F Y') }}</span>
                <span>&bull;</span>
                <span class="text-blue-700 font-semibold">Operator Workstation</span>
            </div>
        </div>
    </div>

    {{-- Right: Actions & User Info --}}
    <div class="flex items-center gap-3 sm:gap-4">

        {{-- Facility Status Pills --}}
        <div class="hidden md:flex items-center gap-2 bg-blue-50/80 px-3 py-1.5 rounded-full border border-blue-200/80 text-[11px] font-mono">
            <span class="inline-flex items-center gap-1 text-blue-900 font-semibold">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> BDG
            </span>
            <span class="text-blue-300">|</span>
            <span class="inline-flex items-center gap-1 text-blue-900 font-semibold">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> SBY
            </span>
        </div>

        {{-- User Profile & Role --}}
        <div class="flex items-center gap-3 pl-2 sm:pl-3 border-l border-slate-200">
            <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-cyan-600 to-blue-700 text-white flex items-center justify-center font-display font-bold text-xs shadow-sm">
                {{ strtoupper(substr(auth()->user()->full_name ?? (auth()->user()->name ?? 'OP'), 0, 2)) }}
            </div>
            <div class="hidden sm:block leading-tight text-left">
                <p class="text-xs font-bold text-slate-900">{{ auth()->user()->full_name ?? (auth()->user()->name ?? 'Operator') }}</p>
                <p class="text-[10px] text-blue-700 font-mono font-medium capitalize">Operator Sterilisasi</p>
            </div>
            
            {{-- Logout Button --}}
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" title="Keluar dari Sistem"
                    class="p-2 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</header>
