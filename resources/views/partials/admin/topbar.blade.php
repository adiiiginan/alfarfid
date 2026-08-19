<header
    class="bg-white border-b border-[var(--line)] px-4 lg:px-8 py-4 flex items-center justify-between sticky top-0 z-20">
    <div class="flex items-center gap-3">
        <button id="sidebarToggle" class="lg:hidden p-2 -ml-2 text-[var(--ink-soft)]">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M4 7h16M4 12h16M4 17h16" />
            </svg>
        </button>
        <div>
            <p class="font-display font-semibold text-lg leading-tight">@yield('page-title', 'Dashboard')</p>
            <p class="text-xs text-[var(--ink-soft)] font-mono">
                {{ now()->translatedFormat('l, d F Y · H:i') }}
            </p>
        </div>
    </div>

    <div class="flex items-center gap-4">
        <span
            class="hidden sm:inline-flex items-center gap-1.5 text-xs font-medium badge-role px-2.5 py-1 rounded-full">
            <span class="w-1.5 h-1.5 rounded-full bg-[var(--navy)]"></span>
            {{ ucfirst(auth()->user()->role) }}
        </span>

        <div class="flex items-center gap-3 pl-4 border-l border-[var(--line)]">
            <div
                class="w-8 h-8 rounded-full bg-[var(--navy-soft)] flex items-center justify-center font-display font-semibold text-[var(--navy)] text-xs">
                {{ strtoupper(substr(auth()->user()->full_name, 0, 2)) }}
            </div>
            <div class="hidden sm:block leading-tight">
                <p class="text-sm font-medium">{{ auth()->user()->full_name }}</p>
                <p class="text-[11px] text-[var(--ink-soft)]">{{ auth()->user()->email }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Keluar"
                    class="p-2 text-[var(--ink-soft)] hover:text-[var(--red)] transition">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.6"
                        viewBox="0 0 24 24">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</header>
