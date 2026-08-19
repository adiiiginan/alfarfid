@extends('layouts.admin')

@section('title', 'Audit Trail')

@section('content')
    <div class="p-6 lg:p-8">

        <div class="mb-6">
            <h1 class="font-display font-semibold text-xl text-gray-900">Audit Trail</h1>
            <p class="text-sm text-gray-500 mt-1">
                Pencatatan perubahan data master (garment, tag, user, dsb.) — siapa mengubah apa dan kapan,
                untuk kebutuhan kepatuhan regulasi (FR-10).
            </p>
        </div>

        {{-- ================= FILTER ================= --}}
        <form method="GET" action="{{ route('admin.audit-trail.index') }}"
            class="bg-white rounded-2xl shadow-sm p-5 mb-6 grid grid-cols-2 md:grid-cols-6 gap-3 items-end">

            <div class="col-span-2 md:col-span-1">
                <label class="block text-[11px] font-semibold uppercase tracking-wide text-gray-500 mb-1">Cari Record
                    ID</label>
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="UUID record..."
                    class="w-full h-10 px-3 rounded-lg border border-gray-200 text-sm focus:border-[var(--copper)] focus:outline-none">
            </div>

            <div>
                <label class="block text-[11px] font-semibold uppercase tracking-wide text-gray-500 mb-1">Tabel</label>
                <select name="table_name"
                    class="w-full h-10 px-3 rounded-lg border border-gray-200 text-sm focus:border-[var(--copper)] focus:outline-none">
                    <option value="">Semua</option>
                    @foreach ($tableNames as $table)
                        <option value="{{ $table }}" @selected(($filters['table_name'] ?? '') === $table)>{{ $table }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-semibold uppercase tracking-wide text-gray-500 mb-1">Aksi</label>
                <select name="action"
                    class="w-full h-10 px-3 rounded-lg border border-gray-200 text-sm focus:border-[var(--copper)] focus:outline-none">
                    <option value="">Semua</option>
                    <option value="insert" @selected(($filters['action'] ?? '') === 'insert')>Insert</option>
                    <option value="update" @selected(($filters['action'] ?? '') === 'update')>Update</option>
                    <option value="delete" @selected(($filters['action'] ?? '') === 'delete')>Delete</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-semibold uppercase tracking-wide text-gray-500 mb-1">Diubah
                    Oleh</label>
                <select name="changed_by"
                    class="w-full h-10 px-3 rounded-lg border border-gray-200 text-sm focus:border-[var(--copper)] focus:outline-none">
                    <option value="">Semua</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->user_id }}" @selected(($filters['changed_by'] ?? '') === $user->user_id)>
                            {{ $user->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-semibold uppercase tracking-wide text-gray-500 mb-1">Dari</label>
                <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"
                    class="w-full h-10 px-3 rounded-lg border border-gray-200 text-sm focus:border-[var(--copper)] focus:outline-none">
            </div>

            <div class="flex gap-2">
                <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"
                    class="w-full h-10 px-3 rounded-lg border border-gray-200 text-sm focus:border-[var(--copper)] focus:outline-none">
            </div>

            <div class="col-span-2 md:col-span-6 flex gap-2 pt-1">
                <button type="submit"
                    class="h-10 px-5 rounded-lg text-sm font-semibold text-white bg-gray-900 hover:bg-gray-800">
                    Filter
                </button>
                <a href="{{ route('admin.audit-trail.index') }}"
                    class="h-10 px-5 rounded-lg text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 flex items-center">
                    Reset
                </a>
            </div>
        </form>

        {{-- ================= TABEL ================= --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-900 text-white text-[11px] uppercase tracking-wide">
                        <th class="text-left px-4 py-3">Waktu</th>
                        <th class="text-left px-4 py-3">Tabel</th>
                        <th class="text-left px-4 py-3">Record</th>
                        <th class="text-left px-4 py-3">Aksi</th>
                        <th class="text-left px-4 py-3">Diubah Oleh</th>
                        <th class="text-left px-4 py-3">Detail Perubahan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50 align-top">
                            <td class="px-4 py-3 whitespace-nowrap text-gray-700">
                                {{ \Illuminate\Support\Carbon::parse($log->changed_at)->format('d M Y, H:i:s') }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-mono text-xs text-gray-600">{{ $log->table_name }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @if ($log->record_label)
                                    <span class="text-gray-900 font-medium">{{ $log->record_label }}</span>
                                    <br>
                                @endif
                                <span class="font-mono text-[11px] text-gray-400">{{ $log->record_id }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $color = match ($log->action) {
                                        'insert' => 'bg-emerald-600',
                                        'update' => 'bg-blue-600',
                                        'delete' => 'bg-red-600',
                                        default => 'bg-gray-500',
                                    };
                                @endphp
                                <span
                                    class="inline-block px-2.5 py-1 rounded-full text-[11px] font-semibold text-white {{ $color }}">
                                    {{ strtoupper($log->action) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-700">
                                {{ $log->changed_by_name ?? '-' }}
                            </td>
                            <td class="px-4 py-3">
                                @if (count($log->changed_fields))
                                    <details class="text-xs">
                                        <summary class="cursor-pointer text-[var(--copper)] font-medium">
                                            {{ count($log->changed_fields) }} field
                                        </summary>
                                        <div class="mt-2 space-y-1.5 max-w-md">
                                            @foreach ($log->changed_fields as $item)
                                                <div class="border border-gray-100 rounded-lg p-2 bg-gray-50">
                                                    <p class="font-mono text-[11px] text-gray-500 mb-0.5">
                                                        {{ $item['field'] }}</p>
                                                    @if ($log->action !== 'insert')
                                                        <p class="text-red-500 line-through break-all">
                                                            {{ json_encode($item['old']) }}</p>
                                                    @endif
                                                    @if ($log->action !== 'delete')
                                                        <p class="text-emerald-600 break-all">
                                                            {{ json_encode($item['new']) }}</p>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </details>
                                @else
                                    <span class="text-gray-400 text-xs">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-10 text-gray-400">
                                Tidak ada data audit trail yang cocok dengan filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="p-4 flex justify-center">
                {{ $logs->links() }}
            </div>
        </div>

    </div>
@endsection
