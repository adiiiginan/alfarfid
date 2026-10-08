@extends('layouts.admin')

@section('title', 'Manajemen Alerts')
@section('page-title', 'Alerts & Notifikasi')

@section('content')
<div class="space-y-6">

    {{-- Header Title --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="font-display font-semibold text-2xl text-slate-900">Manajemen Alerts &amp; Notifikasi</h1>
            <p class="text-sm text-slate-500 mt-1">
                Pemantauan anomali operasional sterilisasi, batas siklus baju/tag, dan status perangkat (FR-03).
            </p>
        </div>
    </div>

    {{-- ================= KARTU STATISTIK ================= --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Open --}}
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 border border-amber-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 font-mono">Open Alerts</p>
                <p class="font-display font-bold text-2xl text-slate-900 mt-0.5">{{ $stats['total_open'] }}</p>
            </div>
        </div>

        {{-- Critical Open --}}
        <div class="rounded-xl border border-rose-200 bg-rose-50/40 p-4 shadow-sm flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center shrink-0 border border-rose-200">
                <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01"/>
                </svg>
            </div>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-rose-700 font-mono">Kritis (Perlu Tindakan)</p>
                <p class="font-display font-bold text-2xl text-rose-700 mt-0.5">{{ $stats['critical_open'] }}</p>
            </div>
        </div>

        {{-- Acknowledged --}}
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
            </div>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 font-mono">Acknowledged</p>
                <p class="font-display font-bold text-2xl text-slate-900 mt-0.5">{{ $stats['acknowledged'] }}</p>
            </div>
        </div>

        {{-- Resolved Today --}}
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 font-mono">Resolved Hari Ini</p>
                <p class="font-display font-bold text-2xl text-emerald-600 mt-0.5">{{ $stats['resolved_today'] }}</p>
            </div>
        </div>
    </div>

    {{-- Alert Flash Message --}}
    @if(session('success'))
        <div class="flex items-start gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            <svg class="w-5 h-5 shrink-0 mt-0.5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- ================= FILTER BAR ================= --}}
    <form method="GET" action="{{ route('admin.alerts.index') }}"
        class="rounded-xl border border-slate-200 bg-white p-5 grid grid-cols-2 md:grid-cols-6 gap-3.5 items-end shadow-sm">

        {{-- Search text --}}
        <div class="col-span-2 md:col-span-2">
            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 font-mono mb-1.5">
                Pencarian
            </label>
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}"
                    placeholder="Kode baju, UID tag, lokasi, pesan..."
                    class="w-full h-10 pl-9 pr-3 rounded-lg border border-slate-300 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[var(--copper)] focus:border-transparent">
            </div>
        </div>

        {{-- Status --}}
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 font-mono mb-1.5">
                Status
            </label>
            <select name="status" class="w-full h-10 px-3 rounded-lg border border-slate-300 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[var(--copper)] focus:border-transparent">
                <option value="all" @selected(($filters['status'] ?? '') === 'all')>Semua Status</option>
                <option value="open" @selected(($filters['status'] ?? '') === 'open')>Open</option>
                <option value="acknowledged" @selected(($filters['status'] ?? '') === 'acknowledged')>Acknowledged</option>
                <option value="resolved" @selected(($filters['status'] ?? '') === 'resolved')>Resolved</option>
            </select>
        </div>

        {{-- Severity --}}
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 font-mono mb-1.5">
                Tingkat (Severity)
            </label>
            <select name="severity" class="w-full h-10 px-3 rounded-lg border border-slate-300 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[var(--copper)] focus:border-transparent">
                <option value="all">Semua</option>
                <option value="critical" @selected(($filters['severity'] ?? '') === 'critical')>Critical (Kritis)</option>
                <option value="warning" @selected(($filters['severity'] ?? '') === 'warning')>Warning (Peringatan)</option>
                <option value="info" @selected(($filters['severity'] ?? '') === 'info')>Info</option>
            </select>
        </div>

        {{-- Date From --}}
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 font-mono mb-1.5">
                Dari Tanggal
            </label>
            <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"
                class="w-full h-10 px-3 rounded-lg border border-slate-300 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[var(--copper)] focus:border-transparent">
        </div>

        {{-- Date To & Submit --}}
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 font-mono mb-1.5">
                Sampai Tanggal
            </label>
            <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"
                class="w-full h-10 px-3 rounded-lg border border-slate-300 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[var(--copper)] focus:border-transparent">
        </div>

        <div class="col-span-2 md:col-span-6 flex gap-2 pt-3 border-t border-slate-100">
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-[var(--copper)] px-4 py-2 text-sm font-medium text-white hover:opacity-90 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4.5h18m-14 5h10m-6 5h2" />
                </svg>
                Terapkan Filter
            </button>
            <a href="{{ route('admin.alerts.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 transition">
                Reset
            </a>
        </div>
    </form>

    {{-- ================= TABEL ALERTS ================= --}}
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden" x-data="{ selectedAlerts: [] }">

        {{-- Bulk Action Bar --}}
        <div class="p-3 bg-blue-50/80 border-b border-blue-100 flex items-center justify-between"
             x-show="selectedAlerts.length > 0" x-cloak>
            <span class="text-xs font-semibold text-blue-800 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-blue-600 inline-block"></span>
                <span x-text="selectedAlerts.length"></span> alert terpilih
            </span>
            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('admin.alerts.bulk-acknowledge') }}" class="inline">
                    @csrf
                    <template x-for="id in selectedAlerts" :key="id">
                        <input type="hidden" name="alert_ids[]" :value="id">
                    </template>
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-white text-blue-700 border border-blue-200 hover:bg-blue-50 transition shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Tandai Dibaca (Acknowledge)
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.alerts.bulk-resolve') }}" class="inline">
                    @csrf
                    <template x-for="id in selectedAlerts" :key="id">
                        <input type="hidden" name="alert_ids[]" :value="id">
                    </template>
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-emerald-600 text-white hover:bg-emerald-700 transition shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        Selesaikan Semua (Resolve)
                    </button>
                </form>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[11px] uppercase tracking-wider text-slate-500 font-semibold border-b border-slate-200 bg-slate-50/75">
                        <th class="px-5 py-3.5 w-10">
                            <input type="checkbox"
                                @change="
                                    if ($event.target.checked) {
                                        selectedAlerts = Array.from(document.querySelectorAll('.alert-checkbox')).map(cb => cb.value);
                                    } else {
                                        selectedAlerts = [];
                                    }
                                "
                                class="rounded border-slate-300 text-[var(--copper)] focus:ring-[var(--copper)]">
                        </th>
                        <th class="px-5 py-3.5 font-medium">Waktu</th>
                        <th class="px-5 py-3.5 font-medium">Tingkat</th>
                        <th class="px-5 py-3.5 font-medium">Tipe &amp; Pesan</th>
                        <th class="px-5 py-3.5 font-medium">Entitas Terkait</th>
                        <th class="px-5 py-3.5 font-medium">Status</th>
                        <th class="px-5 py-3.5 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($alerts as $alert)
                        <tr class="hover:bg-slate-50/80 transition align-middle">
                            {{-- Checkbox --}}
                            <td class="px-5 py-3.5">
                                <input type="checkbox" value="{{ $alert->alert_id }}"
                                    x-model="selectedAlerts"
                                    class="alert-checkbox rounded border-slate-300 text-[var(--copper)] focus:ring-[var(--copper)]">
                            </td>

                            {{-- Waktu --}}
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="font-medium text-slate-900 text-xs">
                                    {{ $alert->triggered_at?->format('d M Y') }}
                                </span>
                                <p class="text-[11px] font-mono text-slate-400 mt-0.5">
                                    {{ $alert->triggered_at?->format('H:i:s') }}
                                </p>
                            </td>

                            {{-- Tingkat (Severity) --}}
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                @if($alert->severity === 'critical')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-600 animate-pulse"></span>
                                        CRITICAL
                                    </span>
                                @elseif($alert->severity === 'warning')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                       WARNING
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                        INFO
                                    </span>
                                @endif
                            </td>

                            {{-- Pesan --}}
                            <td class="px-5 py-3.5 max-w-md">
                                <span class="font-mono text-[10px] uppercase font-semibold text-slate-400 block mb-0.5 tracking-wide">
                                    {{ str_replace('_', ' ', $alert->alert_type) }}
                                </span>
                                <p class="text-slate-800 text-sm leading-snug">
                                    {{ $alert->message }}
                                </p>
                            </td>

                            {{-- Entitas Terkait --}}
                            <td class="px-5 py-3.5 text-xs whitespace-nowrap">
                                @if($alert->garment)
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-slate-400 font-mono text-[10px]">Garment:</span>
                                        <span class="font-mono font-semibold text-slate-900 text-xs">{{ $alert->garment->garment_code }}</span>
                                    </div>
                                @endif
                                @if($alert->tag)
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span class="text-slate-400 font-mono text-[10px]">Tag UID:</span>
                                        <span class="font-mono text-slate-700 text-xs">{{ $alert->tag->tag_uid }}</span>
                                    </div>
                                @endif
                                @if($alert->device)
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span class="text-slate-400 font-mono text-[10px]">Device:</span>
                                        <span class="text-slate-700 text-xs">{{ $alert->device->device_name }} ({{ $alert->device->location }})</span>
                                    </div>
                                @endif
                                @if(!$alert->garment && !$alert->tag && !$alert->device)
                                    <span class="text-slate-400 font-mono text-xs">-</span>
                                @endif
                            </td>

                            {{-- Status & Resolver --}}
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                @if($alert->status === 'open')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                        Open
                                    </span>
                                @elseif($alert->status === 'acknowledged')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        Acknowledged
                                    </span>
                                @elseif($alert->status === 'resolved')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Resolved
                                    </span>
                                    @if($alert->resolvedBy)
                                        <p class="text-[10px] text-slate-400 mt-0.5 font-mono">
                                            oleh {{ $alert->resolvedBy->full_name }}
                                        </p>
                                    @endif
                                @endif
                            </td>

                            {{-- Tombol Aksi --}}
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if($alert->status === 'open')
                                        <form method="POST" action="{{ route('admin.alerts.acknowledge', $alert) }}" class="inline">
                                            @csrf
                                            <button type="submit" title="Tandai Dibaca"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-lg text-blue-700 bg-blue-50 hover:bg-blue-100 transition border border-blue-200 shadow-sm">
                                                Acknowledge
                                            </button>
                                        </form>
                                    @endif

                                    @if($alert->status !== 'resolved')
                                        <form method="POST" action="{{ route('admin.alerts.resolve', $alert) }}" class="inline">
                                            @csrf
                                            <button type="submit" title="Selesaikan Alert"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-lg text-white bg-[var(--copper)] hover:opacity-90 transition shadow-sm">
                                                Resolve
                                            </button>
                                        </form>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-600 bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-lg">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Selesai
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-slate-400">
                                <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" stroke-width="1.4" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Tidak ada alert yang sesuai dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($alerts->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-white">
                {{ $alerts->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
