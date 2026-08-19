@extends('layouts.admin')

@section('title', 'Laporan')
@section('page-title', 'Laporan')

@section('content')

    <div x-data="reportArchivePage()" x-init="init()">

        {{-- HEADER --}}
        <div class="flex items-start justify-between flex-wrap gap-3 mb-6">
            <div>
                <h1 class="font-display font-semibold text-2xl text-[var(--ink)]">Laporan</h1>
                <p class="text-sm text-[var(--ink-soft)] mt-1">
                    Arsip semua laporan yang pernah digenerate — unduh ulang kapan saja sebagai backup.
                </p>
            </div>
        </div>

        {{-- FILTER BAR --}}
        <div class="card p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <div>
                    <label class="text-[11px] font-medium text-[var(--ink-soft)] mb-1 block">Jenis Laporan</label>
                    <select x-model="filters.report_type" @change="fetchLogs(true)"
                        class="w-full text-sm px-3 py-2 rounded-lg border border-[var(--line)] focus:outline-none focus:ring-2 focus:ring-[var(--navy)]/20">
                        <option value="">Semua Jenis</option>
                        @foreach ($reportTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-[11px] font-medium text-[var(--ink-soft)] mb-1 block">Dari Tanggal</label>
                    <input type="date" x-model="filters.date_from" @change="fetchLogs(true)"
                        class="w-full text-sm px-3 py-2 rounded-lg border border-[var(--line)] focus:outline-none focus:ring-2 focus:ring-[var(--navy)]/20">
                </div>
                <div>
                    <label class="text-[11px] font-medium text-[var(--ink-soft)] mb-1 block">Sampai Tanggal</label>
                    <input type="date" x-model="filters.date_to" @change="fetchLogs(true)"
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

        {{-- TABLE ARSIP --}}
        <div class="card overflow-hidden">
            <div class="px-5 py-4 border-b border-[var(--line)] flex items-center justify-between flex-wrap gap-2">
                <div>
                    <h3 class="font-display font-semibold text-sm">Arsip Laporan</h3>
                    <p class="text-xs text-[var(--ink-soft)] mt-0.5">Semua laporan yang pernah digenerate, dari jenis apa
                        pun.</p>
                </div>
                <button @click="fetchLogs(true)"
                    class="text-xs font-medium px-3 py-1.5 rounded-lg border border-[var(--line)] hover:bg-[var(--copper-soft)] transition">
                    Muat Ulang
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr
                            class="text-left text-[11px] uppercase tracking-wider text-[var(--ink-soft)] border-b border-[var(--line)]">
                            <th class="px-5 py-3 font-medium">Waktu Generate</th>
                            <th class="px-5 py-3 font-medium">Jenis Laporan</th>
                            <th class="px-5 py-3 font-medium">Dibuat Oleh</th>
                            <th class="px-5 py-3 font-medium">Periode Data</th>
                            <th class="px-5 py-3 font-medium">Status File</th>
                            <th class="px-5 py-3 font-medium text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--line)]">
                        <template x-if="!loading && logs.length === 0">
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-[var(--ink-soft)] text-sm">
                                    Belum ada laporan yang pernah digenerate.
                                </td>
                            </tr>
                        </template>

                        <template x-for="log in logs" :key="log.report_id">
                            <tr>
                                <td class="px-5 py-3 font-mono text-xs" x-text="log.generated_at"></td>
                                <td class="px-5 py-3">
                                    <span
                                        class="text-[11px] font-medium px-2 py-0.5 rounded-full bg-[var(--navy)]/10 text-[var(--navy)]"
                                        x-text="log.type_label"></span>
                                </td>
                                <td class="px-5 py-3" x-text="log.user_name"></td>
                                <td class="px-5 py-3 text-xs text-[var(--ink-soft)]" x-text="log.date_range"></td>
                                <td class="px-5 py-3">
                                    <span class="text-[11px] font-medium px-2 py-0.5 rounded-full"
                                        :class="log.file_exists ? 'bg-[var(--teal-soft)] text-[var(--teal)]' :
                                            'bg-red-50 text-red-500'"
                                        x-text="log.file_exists ? 'Tersedia' : 'File hilang'"></span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <a :href="log.file_exists ? reissueUrl(log.report_id) : '#'"
                                        :class="log.file_exists ?
                                            'inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition' :
                                            'inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg border border-[var(--line)] opacity-40 cursor-not-allowed'"
                                        @click="!log.file_exists && $event.preventDefault()">
                                        <span aria-hidden="true">📄</span>
                                        <span>Unduh Ulang</span>
                                    </a>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="px-5 py-3 border-t border-[var(--line)] flex items-center justify-between flex-wrap gap-2">
                <span class="text-[11px] text-[var(--ink-soft)]">
                    Halaman <span x-text="page"></span> dari <span x-text="totalPages"></span>
                    &middot; <span x-text="totalItems"></span> total laporan
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
            function reportArchivePage() {
                return {
                    filters: {
                        report_type: '',
                        date_from: '',
                        date_to: ''
                    },

                    logs: [],
                    loading: false,
                    page: 1,
                    totalPages: 1,
                    totalItems: 0,

                    init() {
                        this.fetchLogs(true);
                    },

                    resetFilters() {
                        this.filters = {
                            report_type: '',
                            date_from: '',
                            date_to: ''
                        };
                        this.page = 1;
                        this.fetchLogs(true);
                    },

                    async fetchLogs(showLoading) {
                        if (showLoading) this.loading = true;

                        const params = new URLSearchParams();
                        if (this.filters.report_type) params.set('report_type', this.filters.report_type);
                        if (this.filters.date_from) params.set('date_from', this.filters.date_from);
                        if (this.filters.date_to) params.set('date_to', this.filters.date_to);
                        params.set('page', this.page);
                        params.set('per_page', 15);

                        try {
                            const res = await fetch(`{{ route('admin.reports.data') }}?${params.toString()}`, {
                                headers: {
                                    'Accept': 'application/json'
                                }
                            });
                            const data = await res.json();
                            this.logs = data.logs;
                            this.page = data.pagination.current_page;
                            this.totalPages = data.pagination.last_page;
                            this.totalItems = data.pagination.total;
                        } catch (e) {
                            console.error('Gagal memuat arsip laporan:', e);
                        } finally {
                            this.loading = false;
                        }
                    },

                    prevPage() {
                        if (this.page > 1) {
                            this.page--;
                            this.fetchLogs(true);
                        }
                    },
                    nextPage() {
                        if (this.page < this.totalPages) {
                            this.page++;
                            this.fetchLogs(true);
                        }
                    },

                    reissueUrl(reportId) {
                        return `{{ url('admin/reports/reissue') }}/${reportId}`;
                    }
                }
            }
        </script>
    @endpush

@endsection
