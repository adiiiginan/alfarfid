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
                <span class="px-1.5 py-0.5 rounded text-[9px] font-mono font-bold bg-white/20 text-white border border-white/30">v2.1</span>
            </div>
            <p class="text-[11px] font-mono text-blue-100 truncate">Smart Cleanroom Tracker</p>
        </div>
    </div>

    {{-- Navigation Links (Scrollable Area) --}}
    <nav class="flex-1 px-3 py-4 space-y-5 overflow-y-auto min-h-0">

        {{-- ================= OPERASIONAL ================= --}}
        <div>
            <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-blue-200/90 font-mono mb-1.5">Operasional</p>
            <div class="space-y-1">

                {{-- Dashboard (FR-01) --}}
                <a href="{{ route('admin.dashboard') }}"
                    class="nav-link flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="3" y="3" width="7" height="7" rx="1.5" />
                        <rect x="14" y="3" width="7" height="7" rx="1.5" />
                        <rect x="3" y="14" width="7" height="7" rx="1.5" />
                        <rect x="14" y="14" width="7" height="7" rx="1.5" />
                    </svg>
                    Dashboard
                </a>

                {{-- Master Data Garment & Tag (FR-04) --}}
                <a href="{{ route('admin.garment-config.index') }}"
                    class="nav-link flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium {{ request()->routeIs('admin.garment-config.*') ? 'active' : '' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    Garment &amp; Tag
                </a>

                {{-- Pindah Tag (FR-19 Skenario A) --}}
                <a href="{{ route('admin.pindah-tag.index') }}"
                    class="nav-link flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium {{ request()->routeIs('admin.pindah-tag.*') ? 'active' : '' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    Pindah Tag
                </a>

                {{-- Ganti Tag (FR-19 Skenario B) --}}
                <a href="{{ route('admin.ganti-tag.index') }}"
                    class="nav-link flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium {{ request()->routeIs('admin.ganti-tag.*') ? 'active' : '' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Ganti Tag
                </a>

                {{-- Scan Events (FR-05, FR-11) --}}
                <a href="{{ route('admin.scan-events.index') }}"
                    class="nav-link flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium {{ request()->routeIs('admin.scan-events.*') ? 'active' : '' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Scan Events
                </a>
            </div>
        </div>

        {{-- ================= ANALITIK & LAPORAN ================= --}}
        <div>
            <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-blue-200/90 font-mono mb-1.5">Analitik &amp; Laporan</p>
            <div class="space-y-1">

                {{-- Analytics & Grafik (FR-07, FR-16, FR-17) --}}
                <a href="{{ route('admin.analytics.index') }}"
                    class="nav-link flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium {{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Analytics &amp; Grafik
                </a>

                {{-- Reporting & Export (FR-08) --}}
                <a href="{{ route('admin.reports.index') }}"
                    class="nav-link flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Laporan
                </a>
            </div>
        </div>

        {{-- ================= SISTEM & ADMINISTRASI ================= --}}
        <div>
            <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-blue-200/90 font-mono mb-1.5">Sistem</p>
            <div class="space-y-1">

                {{-- Alerts (FR-03) --}}
                @if (Route::has('admin.alerts.index'))
                    <a href="{{ route('admin.alerts.index') }}"
                        class="nav-link flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium {{ request()->routeIs('admin.alerts.*') ? 'active' : '' }}">
                        <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        Alerts
                        @if(($openAlertsCount ?? 0) > 0)
                            <span class="ml-auto text-[10px] font-mono font-bold bg-rose-500 text-white px-2 py-0.5 rounded-full {{ ($criticalAlertsCount ?? 0) > 0 ? 'animate-pulse' : '' }}">
                                {{ $openAlertsCount }}
                            </span>
                        @endif
                    </a>
                @endif

                {{-- Audit Trail (FR-10) --}}
                <a href="{{ route('admin.audit-trail.index') }}"
                    class="nav-link flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium {{ request()->routeIs('admin.audit-trail.*') ? 'active' : '' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Audit Trail
                </a>

                {{-- Perangkat / Alat RFID --}}
                @if (Route::has('admin.devices.index'))
                    <a href="{{ route('admin.devices.index') }}"
                        class="nav-link flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium {{ request()->routeIs('admin.devices.*') ? 'active' : '' }}">
                        <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <rect x="4" y="4" width="16" height="16" rx="2" />
                            <rect x="9" y="9" width="6" height="6" />
                        </svg>
                        Perangkat &amp; Alat RFID
                    </a>
                @endif

                {{-- Manajemen User (FR-09) --}}
                @if (Route::has('admin.users.index'))
                    <a href="{{ route('admin.users.index') }}"
                        class="nav-link flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Manajemen User
                    </a>
                @endif
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
    </div>

</aside>
