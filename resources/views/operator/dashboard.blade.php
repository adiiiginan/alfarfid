@extends('layouts.operator')

@section('title', 'Scan Events')
@section('page-title', 'Scan Events')

@section('content')

    <div x-data="scanEventsPage()" x-init="init()">

        {{-- HEADER --}}
        <div class="flex items-center justify-between flex-wrap gap-3 mb-6">
            <div>
                <h2 class="font-display font-semibold text-xl">Scan Events</h2>
                <p class="text-sm text-[var(--ink-soft)] mt-1">
                    Log aktivitas scan RFID: baju digunakan, laundry, dan autoclave — diperbarui otomatis secara real-time.
                </p>
                {{--
                    PERUBAHAN: siklus baju (max_cycle_limit) dan siklus tag
                    (rated_max_cycles) beda basis & beda umur — satu tag bisa
                    dipakai ulang lintas beberapa baju lewat Pindah Tag/Ganti
                    Tag. Makanya ditampilkan sebagai 2 kolom terpisah di bawah,
                    bukan digabung jadi satu angka "Siklus" seperti sebelumnya.
                --}}
                <p class="text-[11px] text-[var(--ink-soft)] mt-1 italic">
                    Kolom Siklus Baju &amp; Siklus Tag dihitung terpisah — ambang warnanya sama dengan halaman
                    Analytics (Waspada &ge;70%, Kritis &ge;90%).
                </p>
            </div>
            <div class="flex items-center gap-2">
                <button @click="toggleLive()"
                    class="flex items-center gap-2 text-xs font-medium px-3 py-2 rounded-lg border transition"
                    :class="live ? 'border-[var(--teal)] text-[var(--teal)] bg-[var(--teal-soft)]' :
                        'border-[var(--line)] text-[var(--ink-soft)] hover:bg-[var(--copper-soft)]'">
                    <span class="relative flex h-2 w-2">
                        <span x-show="live"
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[var(--teal)] opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2"
                            :class="live ? 'bg-[var(--teal)]' : 'bg-[var(--ink-soft)]'"></span>
                    </span>
                    <span x-text="live ? 'Live' : 'Dijeda'"></span>
                </button>
                <span class="text-[11px] text-[var(--ink-soft)]" x-show="lastFetchedAt">
                    Update terakhir: <span x-text="lastFetchedAt"></span>
                </span>
            </div>
        </div>

        {{-- STAT CARDS --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="card p-4">
                <p class="text-[11px] uppercase tracking-wider text-[var(--ink-soft)]">Baju Digunakan (Hari Ini)</p>
                <p class="font-display font-semibold text-2xl mt-1 text-[var(--teal)]" x-text="stats.usage"></p>
            </div>
            <div class="card p-4">
                <p class="text-[11px] uppercase tracking-wider text-[var(--ink-soft)]">Laundry (Hari Ini)</p>
                <p class="font-display font-semibold text-2xl mt-1" style="color:#B5651D" x-text="stats.laundry"></p>
            </div>
            <div class="card p-4">
                <p class="text-[11px] uppercase tracking-wider text-[var(--ink-soft)]">Autoclave (Hari Ini)</p>
                <p class="font-display font-semibold text-2xl mt-1 text-[var(--navy)]" x-text="stats.autoclave"></p>
            </div>
            <div class="card p-4">
                <p class="text-[11px] uppercase tracking-wider text-[var(--ink-soft)]">Total Scan (Hari Ini)</p>
                <p class="font-display font-semibold text-2xl mt-1" x-text="stats.total"></p>
            </div>
        </div>

        {{-- TABS KATEGORI --}}
        <div class="flex items-center gap-1 border-b border-[var(--line)] mb-4 overflow-x-auto">
            <template x-for="opt in categoryTabs" :key="opt.value">
                <button @click="setCategory(opt.value)"
                    class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition whitespace-nowrap"
                    :class="filters.category === opt.value ? 'border-[var(--navy)] text-[var(--navy)]' :
                        'border-transparent text-[var(--ink-soft)] hover:text-[var(--ink)]'"
                    x-text="opt.label"></button>
            </template>
        </div>

        {{-- FILTER BAR --}}
        <div class="card p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                <div>
                    <label class="text-[11px] font-medium text-[var(--ink-soft)] mb-1 block">Divisi</label>
                    <select x-model="filters.division_id" @change="fetchData(true)"
                        class="w-full text-sm px-3 py-2 rounded-lg border border-[var(--line)] focus:outline-none focus:ring-2 focus:ring-[var(--navy)]/20">
                        <option value="">Semua Divisi</option>
                        @foreach ($divisions ?? [] as $division)
                            <option value="{{ $division->division_id }}">{{ $division->division_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-[11px] font-medium text-[var(--ink-soft)] mb-1 block">Device / Reader</label>
                    <select x-model="filters.device_id" @change="fetchData(true)"
                        class="w-full text-sm px-3 py-2 rounded-lg border border-[var(--line)] focus:outline-none focus:ring-2 focus:ring-[var(--navy)]/20">
                        <option value="">Semua Device</option>
                        @foreach ($devices ?? [] as $device)
                            <option value="{{ $device->device_id }}">{{ $device->device_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-[11px] font-medium text-[var(--ink-soft)] mb-1 block">Dari Tanggal</label>
                    <input type="date" x-model="filters.date_from" @change="fetchData(true)"
                        class="w-full text-sm px-3 py-2 rounded-lg border border-[var(--line)] focus:outline-none focus:ring-2 focus:ring-[var(--navy)]/20">
                </div>
                <div>
                    <label class="text-[11px] font-medium text-[var(--ink-soft)] mb-1 block">Sampai Tanggal</label>
                    <input type="date" x-model="filters.date_to" @change="fetchData(true)"
                        class="w-full text-sm px-3 py-2 rounded-lg border border-[var(--line)] focus:outline-none focus:ring-2 focus:ring-[var(--navy)]/20">
                </div>
                <div class="flex items-end">
                    <button @click="resetFilters()"
                        class="w-full text-xs font-medium px-4 py-2 rounded-lg border border-[var(--line)] hover:bg-[var(--copper-soft)] transition">
                        Reset Filter
                    </button>
                </div>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="card overflow-hidden">
            <div class="px-5 py-4 border-b border-[var(--line)] flex items-center justify-between">
                <div>
                    <h3 class="font-display font-semibold text-sm">Riwayat Scan</h3>
                    <p class="text-xs text-[var(--ink-soft)] mt-0.5">
                        Dikelompokkan per tanggal &middot; <span x-text="perPage"></span> baris per halaman.
                    </p>
                </div>
                <span class="text-[11px] text-[var(--ink-soft)]" x-text="`${events.length} baris`"></span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr
                            class="text-left text-[11px] uppercase tracking-wider text-[var(--ink-soft)] border-b border-[var(--line)]">
                            <th class="px-5 py-3 font-medium">Waktu</th>
                            <th class="px-5 py-3 font-medium">Kategori</th>
                            <th class="px-5 py-3 font-medium">Tag UID</th>
                            <th class="px-5 py-3 font-medium">Garment</th>
                            <th class="px-5 py-3 font-medium">Divisi</th>
                            <th class="px-5 py-3 font-medium">Device / Lokasi</th>
                            <th class="px-5 py-3 font-medium">Siklus Baju</th>
                            <th class="px-5 py-3 font-medium">Siklus Tag</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--line)]">
                        <template x-if="!loading && events.length === 0">
                            <tr>
                                <td colspan="8" class="px-5 py-10 text-center text-[var(--ink-soft)] text-sm">
                                    Belum ada aktivitas scan yang tercatat untuk filter ini.
                                </td>
                            </tr>
                        </template>

                        <template x-for="group in groupedEvents" :key="group.date">
                            <template x-if="true">
                    <tbody class="contents">
                        {{-- Header tanggal (sticky) --}}
                        <tr>
                            <td colspan="8"
                                class="px-5 py-2 bg-[var(--copper-soft)]/40 text-[11px] font-semibold uppercase tracking-wider text-[var(--ink-soft)] sticky top-0">
                                <span x-text="group.date"></span>
                                <span class="font-normal normal-case ml-1">(<span x-text="group.events.length"></span>
                                    aktivitas)</span>
                            </td>
                        </tr>

                        <template x-for="event in group.events" :key="event.event_id">
                            <tr class="transition-colors duration-1000"
                                :class="isNew(event.event_id) ? 'bg-[var(--teal-soft)]' : ''">
                                <td class="px-5 py-3 font-mono text-xs"
                                    x-text="event.scan_time_h ?? event.scan_timestamp_h"></td>
                                <td class="px-5 py-3">
                                    <span class="text-[11px] font-medium px-2 py-0.5 rounded-full"
                                        :class="categoryBadgeClass(event.category)"
                                        x-text="categoryLabel(event.category)"></span>
                                </td>
                                <td class="px-5 py-3 font-mono text-xs" x-text="event.tag_uid"></td>
                                <td class="px-5 py-3">
                                    <div class="font-medium" x-text="event.garment_code"></div>
                                    <div class="text-[var(--ink-soft)] text-xs" x-text="event.category_name"></div>
                                </td>
                                <td class="px-5 py-3">
                                    <template x-if="event.division_name">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="w-3 h-3 rounded-full border border-[var(--line)] inline-block shrink-0"
                                                :style="`background: ${event.color_hex}`"></span>
                                            <span class="text-[var(--ink-soft)]" x-text="event.division_name"></span>
                                        </div>
                                    </template>
                                    <template x-if="!event.division_name">
                                        <span class="text-[var(--ink-soft)] italic">Belum ditetapkan</span>
                                    </template>
                                </td>
                                <td class="px-5 py-3 text-[var(--ink-soft)]">
                                    <div x-text="event.device_name"></div>
                                    <div class="text-xs" x-text="event.location ?? ''"></div>
                                </td>
                                <td class="px-5 py-3">
                                    <template x-if="garmentZone(event).percent !== null">
                                        <div class="flex flex-col gap-1 min-w-[110px]">
                                            <span class="font-mono text-xs"
                                                x-text="`${event.garment_current_cycle_count}/${event.garment_max_cycle_limit} (${garmentZone(event).percent}%)`"></span>
                                            <div class="h-1.5 w-full rounded-full bg-[var(--line)] overflow-hidden">
                                                <div class="h-full rounded-full" :class="garmentZone(event).className"
                                                    :style="`width:${Math.min(100, garmentZone(event).percent)}%; background-color: currentColor;`">
                                                </div>
                                            </div>
                                            <span
                                                class="inline-flex w-fit text-[10px] font-medium px-2 py-0.5 rounded-full"
                                                :class="garmentZone(event).className"
                                                x-text="garmentZone(event).label"></span>
                                        </div>
                                    </template>
                                    <template x-if="garmentZone(event).percent === null">
                                        <span class="font-mono text-xs text-[var(--ink-soft)]"
                                            x-text="event.cycle_count_after ?? '-'"></span>
                                    </template>
                                </td>
                                <td class="px-5 py-3">
                                    <template x-if="tagZone(event).percent !== null">
                                        <div class="flex flex-col gap-1 min-w-[110px]">
                                            <span class="font-mono text-xs"
                                                x-text="`${event.tag_total_cycles_used}/${event.tag_rated_max_cycles} (${tagZone(event).percent}%)`"></span>
                                            <div class="h-1.5 w-full rounded-full bg-[var(--line)] overflow-hidden">
                                                <div class="h-full rounded-full" :class="tagZone(event).className"
                                                    :style="`width:${Math.min(100, tagZone(event).percent)}%; background-color: currentColor;`">
                                                </div>
                                            </div>
                                            <span
                                                class="inline-flex w-fit text-[10px] font-medium px-2 py-0.5 rounded-full"
                                                :class="tagZone(event).className" x-text="tagZone(event).label"></span>
                                        </div>
                                    </template>
                                    <template x-if="tagZone(event).percent === null">
                                        <span class="text-[var(--ink-soft)] text-xs italic">-</span>
                                    </template>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                    </template>
                    </template>
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-[var(--line)] flex items-center justify-between flex-wrap gap-2">
                <span class="text-[11px] text-[var(--ink-soft)]">
                    Halaman <span x-text="page"></span> dari <span x-text="totalPages"></span>
                    &middot; <span x-text="totalItems"></span> total aktivitas
                </span>
                <div class="flex items-center gap-1">
                    <button @click="prevPage()" :disabled="page <= 1"
                        class="text-xs font-medium px-3 py-1.5 rounded-lg border border-[var(--line)] hover:bg-[var(--copper-soft)] transition disabled:opacity-40 disabled:cursor-not-allowed">
                        &larr; Sebelumnya
                    </button>
                    <button @click="nextPage()" :disabled="page >= totalPages"
                        class="text-xs font-medium px-3 py-1.5 rounded-lg border border-[var(--line)] hover:bg-[var(--copper-soft)] transition disabled:opacity-40 disabled:cursor-not-allowed">
                        Berikutnya &rarr;
                    </button>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
        <script>
            function scanEventsPage() {
                return {
                    events: [],
                    stats: {
                        usage: 0,
                        laundry: 0,
                        autoclave: 0,
                        other: 0,
                        total: 0
                    },
                    filters: {
                        category: 'all',
                        division_id: '',
                        device_id: '',
                        date_from: '',
                        date_to: ''
                    },
                    categoryTabs: [{
                            value: 'all',
                            label: 'Semua'
                        },
                        {
                            value: 'usage',
                            label: 'Baju Digunakan'
                        },
                        {
                            value: 'laundry',
                            label: 'Laundry'
                        },
                        {
                            value: 'autoclave',
                            label: 'Autoclave'
                        },
                        {
                            value: 'other',
                            label: 'Lainnya'
                        }
                    ],
                    loading: false,
                    live: true,
                    pollTimer: null,
                    lastFetchedAt: null,
                    previousIds: new Set(),
                    newIds: new Set(),

                    // ── PAGINATION ──
                    page: 1,
                    perPage: 20,
                    totalPages: 1,
                    totalItems: 0,

                    init() {
                        this.fetchData(true);
                        this.startPolling();
                    },

                    setCategory(value) {
                        this.filters.category = value;
                        this.page = 1;
                        this.fetchData(true);
                    },

                    resetFilters() {
                        this.filters = {
                            category: 'all',
                            division_id: '',
                            device_id: '',
                            date_from: '',
                            date_to: ''
                        };
                        this.page = 1;
                        this.fetchData(true);
                    },

                    toggleLive() {
                        this.live = !this.live;
                        if (this.live) {
                            this.fetchData(false);
                            this.startPolling();
                        } else {
                            clearInterval(this.pollTimer);
                        }
                    },

                    startPolling() {
                        clearInterval(this.pollTimer);
                        this.pollTimer = setInterval(() => {
                            // live-refresh cuma di halaman 1, biar data histori
                            // di halaman lain tidak "geser" saat scan baru masuk
                            if (this.live && this.page === 1) this.fetchData(false);
                        }, 4000);
                    },

                    goToPage(p) {
                        if (p < 1 || p > this.totalPages || p === this.page) return;
                        this.page = p;
                        this.fetchData(true);
                    },

                    prevPage() {
                        this.goToPage(this.page - 1);
                    },
                    nextPage() {
                        this.goToPage(this.page + 1);
                    },

                    async fetchData(showLoading) {
                        if (showLoading) this.loading = true;

                        const params = new URLSearchParams();
                        if (this.filters.category && this.filters.category !== 'all') {
                            params.set('category', this.filters.category);
                        }
                        if (this.filters.division_id) params.set('division_id', this.filters.division_id);
                        if (this.filters.device_id) params.set('device_id', this.filters.device_id);
                        if (this.filters.date_from) params.set('date_from', this.filters.date_from);
                        if (this.filters.date_to) params.set('date_to', this.filters.date_to);
                        params.set('page', this.page);
                        params.set('per_page', this.perPage);

                        try {
                            const res = await fetch(
                                `{{ request()->routeIs('operator.*') ? route('operator.scan-events.data') : route('admin.scan-events.data') }}?${params.toString()}`, {
                                    headers: {
                                        'Accept': 'application/json'
                                    }
                                });
                            const data = await res.json();

                            // highlight baris baru cuma relevan di halaman 1
                            if (this.page === 1) {
                                const currentIds = new Set(data.events.map(e => e.event_id));
                                this.newIds = new Set([...currentIds].filter(id => !this.previousIds.has(id)));
                                this.previousIds = currentIds;
                            } else {
                                this.newIds = new Set();
                            }

                            this.events = data.events;
                            this.stats = data.stats;

                            this.page = data.pagination.current_page;
                            this.totalPages = data.pagination.last_page;
                            this.totalItems = data.pagination.total;
                            this.perPage = data.pagination.per_page;

                            this.lastFetchedAt = new Date().toLocaleTimeString('id-ID');

                            if (this.newIds.size > 0) {
                                setTimeout(() => {
                                    this.newIds = new Set();
                                }, 2500);
                            }
                        } catch (e) {
                            console.error('Gagal memuat scan events:', e);
                        } finally {
                            this.loading = false;
                        }
                    },

                    isNew(id) {
                        return this.newIds.has(id);
                    },

                    // ── GROUPING PER TANGGAL ──
                    // events sudah terurut desc dari backend, jadi cukup group
                    // baris yang scan_date_h-nya sama secara berurutan.
                    get groupedEvents() {
                        const groups = [];
                        let currentDate = null;
                        let currentGroup = null;

                        for (const event of this.events) {
                            const dateLabel = event.scan_date_h ?? '-';
                            if (dateLabel !== currentDate) {
                                currentDate = dateLabel;
                                currentGroup = {
                                    date: dateLabel,
                                    events: []
                                };
                                groups.push(currentGroup);
                            }
                            currentGroup.events.push(event);
                        }
                        return groups;
                    },

                    categoryLabel(cat) {
                        const map = {
                            usage: 'Baju Digunakan',
                            laundry: 'Laundry',
                            autoclave: 'Autoclave',
                            other: 'Lainnya'
                        };
                        return map[cat] ?? cat;
                    },

                    categoryBadgeClass(cat) {
                        const map = {
                            usage: 'bg-[var(--teal-soft)] text-[var(--teal)]',
                            laundry: 'bg-[var(--copper-soft)] text-[#8A4B12]',
                            autoclave: 'bg-[var(--navy)]/10 text-[var(--navy)]',
                            other: 'bg-[var(--line)] text-[var(--ink-soft)]'
                        };
                        return map[cat] ?? 'bg-[var(--line)] text-[var(--ink-soft)]';
                    },

                    cycleZone(used, max) {
                        if (used === undefined || used === null || !max) {
                            return {
                                percent: null,
                                label: '-',
                                className: 'bg-[var(--line)] text-[var(--ink-soft)]'
                            };
                        }
                        const percent = Math.round((used / max) * 1000) / 10;
                        if (percent >= 90) return {
                            percent,
                            label: 'Kritis',
                            className: 'bg-[var(--red-soft)] text-[var(--red)]'
                        };
                        if (percent >= 70) return {
                            percent,
                            label: 'Waspada',
                            className: 'bg-yellow-100 text-yellow-700'
                        };
                        return {
                            percent,
                            label: 'Aman',
                            className: 'bg-[var(--teal-soft)] text-[var(--teal)]'
                        };
                    },

                    garmentZone(event) {
                        return this.cycleZone(event.garment_current_cycle_count, event.garment_max_cycle_limit);
                    },

                    tagZone(event) {
                        return this.cycleZone(event.tag_total_cycles_used, event.tag_rated_max_cycles);
                    }
                }
            }
        </script>
    @endpush

@endsection
