@extends('layouts.admin')

@section('title', 'Audit Trail')
@section('page-title', 'Audit Trail')

@section('content')
    <div class="space-y-6">

        {{-- Header Title --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="font-display font-semibold text-2xl text-slate-900">Audit Trail</h1>
                <p class="text-sm text-slate-500 mt-1">
                    Pencatatan perubahan data master (garment, tag, user, dsb.) — siapa mengubah apa dan kapan,
                    untuk kebutuhan kepatuhan regulasi (FR-10).
                </p>
            </div>
        </div>

        {{-- ================= FILTER ================= --}}
        <form method="GET" action="{{ route('admin.audit-trail.index') }}"
            class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm grid grid-cols-2 md:grid-cols-6 gap-3.5 items-end mb-6">

            <div class="col-span-2 md:col-span-1">
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 font-mono mb-1.5">
                    Cari Record ID
                </label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="UUID record..."
                        class="w-full h-10 pl-9 pr-3 rounded-lg border border-slate-300 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[var(--copper)] focus:border-transparent">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 font-mono mb-1.5">
                    Tabel
                </label>
                <select name="table_name"
                    class="w-full h-10 px-3 rounded-lg border border-slate-300 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[var(--copper)] focus:border-transparent">
                    <option value="">Semua Tabel</option>
                    @foreach ($tableNames as $table)
                        <option value="{{ $table }}" @selected(($filters['table_name'] ?? '') === $table)>{{ $table }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 font-mono mb-1.5">
                    Aksi
                </label>
                <select name="action"
                    class="w-full h-10 px-3 rounded-lg border border-slate-300 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[var(--copper)] focus:border-transparent">
                    <option value="">Semua Aksi</option>
                    <option value="insert" @selected(($filters['action'] ?? '') === 'insert')>Insert</option>
                    <option value="update" @selected(($filters['action'] ?? '') === 'update')>Update</option>
                    <option value="delete" @selected(($filters['action'] ?? '') === 'delete')>Delete</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 font-mono mb-1.5">
                    Diubah Oleh
                </label>
                <select name="changed_by"
                    class="w-full h-10 px-3 rounded-lg border border-slate-300 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[var(--copper)] focus:border-transparent">
                    <option value="">Semua User</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->user_id }}" @selected(($filters['changed_by'] ?? '') === $user->user_id)>
                            {{ $user->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 font-mono mb-1.5">
                    Dari Tanggal
                </label>
                <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"
                    class="w-full h-10 px-3 rounded-lg border border-slate-300 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[var(--copper)] focus:border-transparent">
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 font-mono mb-1.5">
                    Sampai Tanggal
                </label>
                <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"
                    class="w-full h-10 px-3 rounded-lg border border-slate-300 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[var(--copper)] focus:border-transparent">
            </div>

            <div class="col-span-2 md:col-span-6 flex gap-2 pt-3 border-t border-slate-100">
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-[var(--copper)] px-4 py-2 text-sm font-medium text-white hover:opacity-90 transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4.5h18m-14 5h10m-6 5h2" />
                    </svg>
                    Terapkan Filter
                </button>
                <a href="{{ route('admin.audit-trail.index') }}"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 transition">
                    Reset
                </a>
            </div>
        </form>

        {{-- ================= TABEL ================= --}}
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-[11px] uppercase tracking-wider text-slate-500 font-semibold border-b border-slate-200 bg-slate-50/75">
                            <th class="px-5 py-3.5 font-medium">Waktu</th>
                            <th class="px-5 py-3.5 font-medium">Tabel</th>
                            <th class="px-5 py-3.5 font-medium">Record</th>
                            <th class="px-5 py-3.5 font-medium">Aksi</th>
                            <th class="px-5 py-3.5 font-medium">Diubah Oleh</th>
                            <th class="px-5 py-3.5 font-medium">Detail Perubahan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($logs as $log)
                            <tr class="hover:bg-slate-50/80 transition align-top">
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="font-medium text-slate-900 text-xs">
                                        {{ \Illuminate\Support\Carbon::parse($log->changed_at)->format('d M Y') }}
                                    </span>
                                    <p class="text-[11px] font-mono text-slate-400 mt-0.5">
                                        {{ \Illuminate\Support\Carbon::parse($log->changed_at)->format('H:i:s') }}
                                    </p>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="font-mono text-xs font-semibold px-2 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $log->table_name }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    @if ($log->record_label)
                                        <span class="text-slate-900 font-medium text-xs">{{ $log->record_label }}</span>
                                    @endif
                                    <span class="font-mono text-[11px] text-slate-400 block break-all {{ $log->record_label ? 'mt-0.5' : '' }}">
                                        {{ $log->record_id }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    @if ($log->action === 'insert')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            INSERT
                                        </span>
                                    @elseif ($log->action === 'update')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                            UPDATE
                                        </span>
                                    @elseif ($log->action === 'delete')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                            DELETE
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                            {{ strtoupper($log->action) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 border border-slate-200 flex items-center justify-center text-[10px] font-bold uppercase shrink-0">
                                            {{ substr($log->changed_by_name ?? 'S', 0, 1) }}
                                        </div>
                                        <span class="text-xs font-medium text-slate-800">
                                            {{ $log->changed_by_name ?? 'Sistem' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5">
                                    @if (count($log->changed_fields))
                                        <details class="text-xs group">
                                            <summary class="cursor-pointer font-medium text-[var(--copper)] hover:underline inline-flex items-center gap-1 select-none">
                                                <svg class="w-3.5 h-3.5 transition-transform group-open:rotate-90 text-[var(--copper)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                                </svg>
                                                <span>{{ count($log->changed_fields) }} field diubah</span>
                                            </summary>
                                            <div class="mt-2 space-y-1.5 max-w-md">
                                                @foreach ($log->changed_fields as $item)
                                                    <div class="border border-slate-200 rounded-lg p-2.5 bg-slate-50/70 text-xs">
                                                        <p class="font-mono text-[11px] font-semibold text-slate-700 mb-1">
                                                            {{ $item['field'] }}
                                                        </p>
                                                        @if ($log->action !== 'insert')
                                                            <div class="flex items-start gap-1.5 text-rose-600 font-mono text-[11px]">
                                                                <span class="shrink-0 text-rose-400 font-sans">&minus;</span>
                                                                <span class="line-through break-all">{{ json_encode($item['old'], JSON_UNESCAPED_UNICODE) }}</span>
                                                            </div>
                                                        @endif
                                                        @if ($log->action !== 'delete')
                                                            <div class="flex items-start gap-1.5 text-emerald-700 font-mono text-[11px] mt-0.5">
                                                                <span class="shrink-0 text-emerald-500 font-sans">&plus;</span>
                                                                <span class="break-all font-semibold">{{ json_encode($item['new'], JSON_UNESCAPED_UNICODE) }}</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </details>
                                    @else
                                        <span class="text-slate-400 font-mono text-xs">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-12 text-slate-400">
                                    <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" stroke-width="1.4" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                    Tidak ada data audit trail yang cocok dengan filter ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($logs->hasPages())
                <div class="px-6 py-4 border-t border-slate-100 bg-white">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection
