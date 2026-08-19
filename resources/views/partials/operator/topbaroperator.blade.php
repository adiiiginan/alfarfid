<header class="bg-[var(--surface)] border-b border-[var(--line)] px-4 lg:px-8 py-4">
    <div class="flex items-center justify-between gap-4">

        <div class="flex items-center gap-3 min-w-0">
            {{-- Tombol buka sidebar khusus mobile --}}
            <button id="sidebarToggle"
                class="lg:hidden shrink-0 w-9 h-9 flex items-center justify-center rounded-lg border border-[var(--line)] text-[var(--ink-soft)] hover:bg-[var(--copper-soft)] transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <h1 class="font-display font-semibold text-lg text-[var(--ink)] truncate">
                @yield('page-title', 'Dashboard')
            </h1>
        </div>

        <div class="flex items-center gap-3 shrink-0">
            <div class="text-right hidden sm:block">
                <p class="text-sm font-medium text-[var(--ink)] leading-tight">
                    {{ auth()->user()->full_name ?? (auth()->user()->name ?? 'Operator') }}
                </p>
                <span class="badge-role inline-block text-[10px] font-medium px-2 py-0.5 rounded-full mt-0.5">
                    Operator
                </span>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="text-xs font-medium px-3 py-2 rounded-lg border border-[var(--line)] text-[var(--ink-soft)] hover:bg-[var(--red-soft)] hover:text-[var(--red)] transition">
                    Keluar
                </button>
            </form>
        </div>

    </div>
</header>
