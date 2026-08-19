<aside id="sidebar"
    class="sidebar fixed lg:static z-40 w-64 min-h-screen flex flex-col transition-transform duration-200">

    <div class="flex items-center gap-3 px-6 py-6 border-b border-white/10">
        <div
            class="w-9 h-9 rounded-lg bg-[var(--copper)] flex items-center justify-center font-display font-bold text-white text-sm">
            AF
        </div>
        <div>
            <p class="font-display font-semibold text-white text-sm tracking-wide">AlfarFID</p>
            <p class="text-[10px] font-mono text-white/35 uppercase tracking-widest">Super Admin</p>
        </div>
    </div>

    <nav class="flex-1 px-3 py-6 space-y-6 overflow-y-auto">

        {{-- ================= OPERASIONAL ================= --}}
        <div>
            <p class="px-3 text-[10px] font-semibold uppercase tracking-widest text-white/35 mb-2">Operasional</p>
            <div class="space-y-1">

                {{-- Dashboard (FR-01) --}}
                <a href="{{ route('admin.dashboard') }}"
                    class="nav-link flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.6"
                        viewBox="0 0 24 24">
                        <rect x="3" y="3" width="7" height="7" rx="1.5" />
                        <rect x="14" y="3" width="7" height="7" rx="1.5" />
                        <rect x="3" y="14" width="7" height="7" rx="1.5" />
                        <rect x="14" y="14" width="7" height="7" rx="1.5" />
                    </svg>
                    Dashboard
                </a>

                {{-- Master Data Garment & Tag (FR-04) --}}
                <a href="{{ route('admin.garment-config.index') }}"
                    class="nav-link flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.garment-config.*') ? 'active' : '' }}">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.6"
                        viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="3.2" />
                        <path d="M12 3v2.5M12 18.5V21M21 12h-2.5M5.5 12H3" />
                        <path d="M18.4 5.6 16.6 7.4M7.4 16.6 5.6 18.4M18.4 18.4l-1.8-1.8M7.4 7.4 5.6 5.6" />
                    </svg>
                    Garment &amp; Tag
                </a>

                {{-- Pindah Tag — Skenario A: baju rusak, tag masih bagus (FR-19) --}}
                <a href="{{ route('admin.pindah-tag.index') }}"
                    class="nav-link flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.pindah-tag.*') ? 'active' : '' }}">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.6"
                        viewBox="0 0 24 24">
                        <path d="M17 2.5 21 6l-4 3.5M21 6H8a5 5 0 0 0-5 5" />
                        <path d="M7 21.5 3 18l4-3.5M3 18h13a5 5 0 0 0 5-5" />
                    </svg>
                    Pindah Tag
                </a>

                {{-- Ganti Tag — Skenario B: baju masih bagus, tag aus (FR-19) --}}
                <a href="{{ route('admin.ganti-tag.index') }}"
                    class="nav-link flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.ganti-tag.*') ? 'active' : '' }}">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.6"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8.25 3.75H6A2.25 2.25 0 0 0 3.75 6v2.25M8.25 20.25H6A2.25 2.25 0 0 1 3.75 18v-2.25m12-12H18A2.25 2.25 0 0 1 20.25 6v2.25m-12 12H18A2.25 2.25 0 0 0 20.25 18v-2.25M9 12h6" />
                    </svg>
                    Ganti Tag
                </a>

                {{-- Sesi Autoclave (FR-18) --}}
                <!--<a href=""
                    class="nav-link flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.sessions.*') ? 'active' : '' }}">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.6"
                        viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="8.5" />
                        <path d="M12 7v5l3 2" />
                    </svg>
                    Sesi Autoclave
                </a>-->

                {{-- Scan Events / Log Riwayat (FR-05, FR-11) --}}
                <a href="{{ route('admin.scan-events.index') }}"
                    class="nav-link flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.scan-events.*') ? 'active' : '' }}">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.6"
                        viewBox="0 0 24 24">
                        <path d="M3 12h4l2-7 4 14 2-7h6" />
                    </svg>
                    Scan Events
                </a>
            </div>
        </div>

        {{-- ================= ANALITIK & LAPORAN ================= --}}
        <div>
            <p class="px-3 text-[10px] font-semibold uppercase tracking-widest text-white/35 mb-2">Analitik &amp;
                Laporan</p>
            <div class="space-y-1">

                {{-- Analytics & Grafik (FR-07) --}}
                <a href="{{ route('admin.analytics.index') }}"
                    class="nav-link flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.6"
                        viewBox="0 0 24 24">
                        <path d="M4 20V10M12 20V4M20 20v-7" />
                    </svg>
                    Analytics &amp; Grafik
                </a>

                {{-- Proyeksi Retired & Forecast Stok (FR-16, FR-17) --}}
                <!--<a href=""
                    class="nav-link flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.forecast.*') ? 'active' : '' }}">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.6"
                        viewBox="0 0 24 24">
                        <path d="M4 4v16h16" />
                        <path d="M7 15l3.5-4 3 3L19 8" />
                        <circle cx="19" cy="8" r="1.4" />
                    </svg>
                    Proyeksi &amp; Forecast Stok
                </a>-->

                {{-- Reporting & Export (FR-08) --}}
                <a href="{{ route('admin.reports.index') }}"
                    class="nav-link flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.6"
                        viewBox="0 0 24 24">
                        <path d="M7 3h7l4 4v14H7z" />
                        <path d="M14 3v4h4M9 13h6M9 17h6" />
                    </svg>
                    Laporan
                </a>
            </div>
        </div>

        {{-- ================= SISTEM & ADMINISTRASI ================= --}}
        <div>
            <p class="px-3 text-[10px] font-semibold uppercase tracking-widest text-white/35 mb-2">Sistem</p>
            <div class="space-y-1">

                {{-- Device Monitoring (FR-06) — belum ada controller/route, tetap nonaktif sampai dibuat --}}
                @if (Route::has('admin.devices.index'))
                    <a href="{{ route('admin.devices.index') }}"
                        class="nav-link flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.devices.*') ? 'active' : '' }}">
                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.6"
                            viewBox="0 0 24 24">
                            <rect x="4" y="4" width="16" height="16" rx="2" />
                            <rect x="9" y="9" width="6" height="6" />
                            <path d="M9 2v2M15 2v2M9 20v2M15 20v2M2 9h2M2 15h2M20 9h2M20 15h2" />
                        </svg>
                        Devices
                    </a>
                @endif

                {{-- Alerts / Threshold Notifikasi (FR-03) — belum ada controller/route, tetap nonaktif sampai dibuat --}}
                @if (Route::has('admin.alerts.index'))
                    <a href="{{ route('admin.alerts.index') }}"
                        class="nav-link flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.alerts.*') ? 'active' : '' }}">
                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.6"
                            viewBox="0 0 24 24">
                            <path d="M12 3a5 5 0 0 0-5 5v3.5L5 15h14l-2-3.5V8a5 5 0 0 0-5-5Z" />
                            <path d="M9.5 19a2.5 2.5 0 0 0 5 0" />
                        </svg>
                        Alerts
                        <span
                            class="ml-auto text-[10px] font-mono bg-[var(--red)] text-white px-1.5 py-0.5 rounded-full">
                            {{ $openAlerts ?? 3 }}
                        </span>
                    </a>
                @endif

                {{-- Audit Trail perubahan data master (FR-10) --}}
                <a href="{{ route('admin.audit-trail.index') }}"
                    class="nav-link flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.audit-trail.*') ? 'active' : '' }}">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.6"
                        viewBox="0 0 24 24">
                        <path d="M9 3h9v18H6V6l3-3Z" />
                        <path d="M9 3v3H6" />
                        <path d="M9.5 12h5M9.5 15.5h5" />
                    </svg>
                    Audit Trail
                </a>

                {{-- Manajemen User & RBAC (FR-09) — khusus Super Admin --}}
                @if (Route::has('admin.users.index'))
                    <a href="{{ route('admin.users.index') }}"
                        class="nav-link flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.6"
                            viewBox="0 0 24 24">
                            <circle cx="9" cy="8" r="3.2" />
                            <circle cx="17" cy="9" r="2.4" />
                            <path d="M3 20c0-3.3 2.7-5.5 6-5.5s6 2.2 6 5.5M15.5 14.7c2.5.3 4.5 2.2 4.5 5.3" />
                        </svg>
                        Manajemen User
                    </a>
                @endif

                {{-- Pengaturan Sistem: threshold siklus, lokasi/mesin, integrasi API (FR-12, FR-14) — khusus Super Admin --}}
                @if (Route::has('admin.settings.index'))
                    <a href="{{ route('admin.settings.index') }}"
                        class="nav-link flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.6"
                            viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="3" />
                            <path
                                d="M19.4 13.5a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V19.4a2 2 0 1 1-4 0v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H4.6a2 2 0 1 1 0-4h.09a1.65 1.65 0 0 0 1.51-1 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H10.5a1.65 1.65 0 0 0 1-1.51V4.6a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V10.5a1.65 1.65 0 0 0 1.51 1H19.4a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z" />
                        </svg>
                        Pengaturan Sistem
                    </a>
                @endif
            </div>
        </div>
    </nav>

</aside>
