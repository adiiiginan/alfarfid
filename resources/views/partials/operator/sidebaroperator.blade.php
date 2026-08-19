<aside id="sidebar"
    class="sidebar fixed lg:static z-40 w-64 min-h-screen flex flex-col transition-transform duration-200">

    <div class="flex items-center gap-3 px-6 py-6 border-b border-white/10">
        <div
            class="w-9 h-9 rounded-lg bg-[var(--copper)] flex items-center justify-center font-display font-bold text-white text-sm">
            AF
        </div>
        <div>
            <p class="font-display font-semibold text-white text-sm tracking-wide">AlfarFID</p>
            <p class="text-[10px] font-mono text-white/35 uppercase tracking-widest">Operator</p>
        </div>
    </div>

    <nav class="flex-1 px-3 py-6 space-y-6 overflow-y-auto">
        <div>
            <p class="px-3 text-[10px] font-semibold uppercase tracking-widest text-white/35 mb-2">Operasional</p>
            <div class="space-y-1">

                {{-- Dashboard operator = halaman Scan Events --}}
                <a href="{{ route('operator.dashboard') }}"
                    class="nav-link flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('operator.dashboard') ? 'active' : '' }}">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.6"
                        viewBox="0 0 24 24">
                        <rect x="3" y="3" width="7" height="7" rx="1.5" />
                        <rect x="14" y="3" width="7" height="7" rx="1.5" />
                        <rect x="3" y="14" width="7" height="7" rx="1.5" />
                        <rect x="14" y="14" width="7" height="7" rx="1.5" />
                    </svg>
                    Dashboard
                </a>
            </div>
        </div>
    </nav>

</aside>
