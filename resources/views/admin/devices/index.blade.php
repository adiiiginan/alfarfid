@extends('layouts.admin')

@section('title', 'Manajemen Perangkat & Alat RFID')
@section('page-title', 'Perangkat & Alat RFID')

@section('content')
<div class="space-y-6">

    {{-- ================= HEADER ================= --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="font-display font-bold text-2xl text-slate-900 tracking-tight">Manajemen Perangkat &amp; Alat RFID</h1>
            <p class="text-sm text-slate-500 mt-1">
                Registrasi reader RFID (ESP32/UHF), pemetaan lokasi pabrik (Bandung, Surabaya, dsb.), dan manajemen API Key perangkat.
            </p>
        </div>
        <button onclick="openCreateModal()"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-700 hover:bg-blue-800 text-white text-sm font-bold shadow-md shadow-blue-700/20 transition self-start sm:self-auto font-display">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            <span>Tambah Alat Baru</span>
        </button>
    </div>

    {{-- ================= FLASH MESSAGES ================= --}}
    @if (session('success'))
        <div class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800 shadow-xs">
            <svg class="w-5 h-5 shrink-0 mt-0.5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <div class="flex-1">
                <p class="font-bold">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="flex items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-800 shadow-xs">
            <svg class="w-5 h-5 shrink-0 mt-0.5 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <div class="flex-1">
                <p class="font-bold">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    {{-- ================= API KEY GENERATED ALERT BOX ================= --}}
    @if (session('generated_api_key'))
        <div class="rounded-2xl border-2 border-blue-400 bg-gradient-to-r from-blue-50 to-indigo-50 p-5 shadow-sm space-y-3">
            <div class="flex items-center gap-2 text-blue-900 font-bold text-base">
                <svg class="w-5 h-5 text-blue-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                </svg>
                <span>Kredensial API Key Perangkat ESP32: {{ session('generated_device_name') }}</span>
            </div>
            <p class="text-xs text-blue-800 leading-relaxed">
                Salin API Key di bawah ini dan masukkan ke konfigurasi firmware reader ESP32. 
                <strong class="text-blue-950 underline">Kunci ini hanya ditampilkan sekali demi keamanan.</strong>
            </p>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                <div class="flex-1 bg-white border border-blue-200 rounded-xl px-3.5 py-2.5 font-mono text-sm text-slate-800 select-all font-semibold flex items-center justify-between">
                    <span id="apiKeyText">{{ session('generated_api_key') }}</span>
                </div>
                <button type="button" onclick="copyApiKey()"
                    class="px-4 py-2.5 rounded-xl bg-blue-700 hover:bg-blue-800 text-white text-xs font-bold transition shadow-sm flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    <span id="copyBtnText">Salin API Key</span>
                </button>
            </div>

            <div class="text-[11px] font-mono text-blue-700 bg-white/70 rounded-lg p-2.5 border border-blue-100">
                <strong>Header HTTP yang Wajib Dikirim Reader:</strong><br>
                <code>X-Device-Mac: {{ session('generated_mac') }}</code><br>
                <code>X-Api-Key: {{ session('generated_api_key') }}</code>
            </div>
        </div>
    @endif

    {{-- ================= 4 KARTU STATISTIK ALAT ================= --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total --}}
        <div class="card card-hover p-4 flex items-center gap-3.5 shadow-xs">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center shrink-0 border border-blue-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <rect x="4" y="4" width="16" height="16" rx="2" />
                    <rect x="9" y="9" width="6" height="6" />
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 font-mono">Total Reader</p>
                <p class="font-display font-extrabold text-2xl text-slate-900">{{ number_format($stats['total']) }}</p>
            </div>
        </div>

        {{-- Online --}}
        <div class="card card-hover p-4 flex items-center gap-3.5 shadow-xs">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100">
                <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.288 15.038a5.25 5.25 0 017.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 011.06 0z" />
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 font-mono">Online</p>
                <p class="font-display font-extrabold text-2xl text-emerald-700">{{ number_format($stats['online']) }}</p>
            </div>
        </div>

        {{-- Offline --}}
        <div class="card card-hover p-4 flex items-center gap-3.5 shadow-xs">
            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center shrink-0 border border-slate-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M9.88 9.88a3 3 0 104.24 4.24M12 21a9 9 0 01-9-9c0-1.74.49-3.37 1.34-4.75" />
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 font-mono">Offline</p>
                <p class="font-display font-extrabold text-2xl text-slate-600">{{ number_format($stats['offline']) }}</p>
            </div>
        </div>

        {{-- Maintenance --}}
        <div class="card card-hover p-4 flex items-center gap-3.5 shadow-xs">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 border border-amber-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.07a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091.547.07 1.125-.07 1.644" />
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 font-mono">Maintenance</p>
                <p class="font-display font-extrabold text-2xl text-amber-700">{{ number_format($stats['maintenance']) }}</p>
            </div>
        </div>
    </div>

    {{-- ================= FILTER BAR ================= --}}
    <form method="GET" action="{{ route('admin.devices.index') }}" class="card p-4 shadow-xs">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            {{-- Search --}}
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 font-mono mb-1">Cari Nama / MAC / Lokasi</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Contoh: Bandung, Surabaya, 24:6F..."
                    class="w-full h-10 px-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
            </div>

            {{-- Status --}}
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 font-mono mb-1">Status Alat</label>
                <select name="status" class="w-full h-10 px-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="">Semua Status</option>
                    <option value="online" {{ $status === 'online' ? 'selected' : '' }}>Online</option>
                    <option value="offline" {{ $status === 'offline' ? 'selected' : '' }}>Offline</option>
                    <option value="maintenance" {{ $status === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                </select>
            </div>

            {{-- Divisi --}}
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 font-mono mb-1">Divisi Penempatan</label>
                <select name="division_id" class="w-full h-10 px-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="">Semua Divisi</option>
                    @foreach ($divisions as $div)
                        <option value="{{ $div->division_id }}" {{ $divisionId === $div->division_id ? 'selected' : '' }}>
                            {{ $div->division_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Actions --}}
            <div class="flex items-end gap-2">
                <button type="submit"
                    class="flex-1 h-10 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition flex items-center justify-center gap-1.5 font-display">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8" />
                        <path d="M21 21l-4.35-4.35" />
                    </svg>
                    Filter
                </button>
                <a href="{{ route('admin.devices.index') }}"
                    class="h-10 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition flex items-center justify-center font-display">
                    Reset
                </a>
            </div>
        </div>
    </form>

    {{-- ================= TABEL DAFTAR ALAT ================= --}}
    <div class="card overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-[11px] font-mono uppercase tracking-wider text-slate-500">
                        <th class="py-3.5 px-4 font-bold">Nama Alat / Reader</th>
                        <th class="py-3.5 px-4 font-bold">MAC Address</th>
                        <th class="py-3.5 px-4 font-bold">Lokasi / Fasilitas</th>
                        <th class="py-3.5 px-4 font-bold">Divisi</th>
                        <th class="py-3.5 px-4 font-bold">Status</th>
                        <th class="py-3.5 px-4 font-bold">Heartbeat</th>
                        <th class="py-3.5 px-4 font-bold text-center">Total Scan</th>
                        <th class="py-3.5 px-4 font-bold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($devices as $dev)
                        @php
                            $isBdg = str_contains(strtolower($dev->location ?? ''), 'bandung');
                            $isSby = str_contains(strtolower($dev->location ?? ''), 'surabaya');
                            $statusBadge = match($dev->status) {
                                'online' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'maintenance' => 'bg-amber-50 text-amber-700 border-amber-200',
                                default => 'bg-slate-100 text-slate-600 border-slate-200',
                            };
                        @endphp
                        <tr class="hover:bg-blue-50/30 transition">
                            {{-- Nama Alat --}}
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900">{{ $dev->device_name }}</div>
                                <div class="text-[11px] font-mono text-slate-400">ID: {{ substr($dev->device_id, 0, 8) }}...</div>
                            </td>

                            {{-- MAC Address --}}
                            <td class="py-3.5 px-4 font-mono text-xs text-blue-900 font-semibold select-all">
                                {{ strtoupper($dev->mac_address) }}
                            </td>

                            {{-- Lokasi & Facility Badge --}}
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    @if($isBdg)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                            Bandung
                                        </span>
                                    @elseif($isSby)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            Surabaya
                                        </span>
                                    @endif
                                    <span class="text-xs text-slate-700 font-medium">{{ $dev->location }}</span>
                                </div>
                            </td>

                            {{-- Divisi --}}
                            <td class="py-3.5 px-4 text-xs text-slate-600">
                                @if($dev->division)
                                    <span class="inline-flex items-center gap-1.5 font-medium">
                                        <span class="w-2 h-2 rounded-full" style="background-color: {{ $dev->division->color_hex ?? '#2563EB' }};"></span>
                                        {{ $dev->division->division_name }}
                                    </span>
                                @else
                                    <span class="text-slate-400 italic">Umum / Semua</span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-mono font-bold border {{ $statusBadge }}">
                                    @if($dev->status === 'online')
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    @endif
                                    {{ ucfirst($dev->status) }}
                                </span>
                            </td>

                            {{-- Last Heartbeat --}}
                            <td class="py-3.5 px-4 text-xs text-slate-500 font-mono">
                                {{ $dev->last_heartbeat_at ? $dev->last_heartbeat_at->diffForHumans() : '—' }}
                            </td>

                            {{-- Total Scan --}}
                            <td class="py-3.5 px-4 text-center font-display font-bold text-xs text-slate-800">
                                {{ number_format($dev->scan_events_count ?? 0) }}
                            </td>

                            {{-- Actions --}}
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    {{-- Edit Button --}}
                                    <button type="button" onclick='openEditModal(@json($dev))'
                                        title="Edit Data Alat"
                                        class="p-1.5 rounded-lg text-slate-600 hover:text-blue-700 hover:bg-blue-50 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>

                                    {{-- Regenerate Key Form --}}
                                    <form method="POST" action="{{ route('admin.devices.regenerate-key', $dev->device_id) }}" class="inline"
                                        onsubmit="return confirm('Buat API Key baru untuk alat {{ $dev->device_name }}? API Key lama tidak akan berlaku lagi.');">
                                        @csrf
                                        <button type="submit" title="Regenerate API Key"
                                            class="p-1.5 rounded-lg text-slate-600 hover:text-amber-700 hover:bg-amber-50 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                                            </svg>
                                        </button>
                                    </form>

                                    {{-- Delete Form --}}
                                    <form method="POST" action="{{ route('admin.devices.destroy', $dev->device_id) }}" class="inline"
                                        onsubmit="return confirm('Yakin ingin menghapus/menonaktifkan alat {{ $dev->device_name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus Alat"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-12 text-slate-400 font-mono text-xs">
                                Tidak ada perangkat reader RFID yang sesuai dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($devices->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">
                {{ $devices->links() }}
            </div>
        @endif
    </div>

</div>

{{-- ================= MODAL TAMBAH ALAT BARU ================= --}}
<div id="createDeviceModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs hidden p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-6 sm:p-7 border border-slate-200 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div>
                <h3 class="font-display font-bold text-lg text-slate-900">Tambah Alat Reader RFID Baru</h3>
                <p class="text-xs text-slate-500 font-mono mt-0.5">Registrasi reader ESP32 &amp; generate API Key unik</p>
            </div>
            <button onclick="closeCreateModal()" class="text-slate-400 hover:text-slate-700 p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form method="POST" action="{{ route('admin.devices.store') }}" class="space-y-4 mt-5">
            @csrf

            {{-- Nama Alat --}}
            <div>
                <label class="block text-xs font-bold font-mono uppercase tracking-wider text-slate-700 mb-1.5">Nama Alat / Reader <span class="text-rose-500">*</span></label>
                <input type="text" name="device_name" required value="{{ old('device_name') }}"
                    placeholder="Contoh: Reader Autoclave Line 1 (Bandung)"
                    class="w-full h-11 px-3.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            {{-- MAC Address --}}
            <div>
                <label class="block text-xs font-bold font-mono uppercase tracking-wider text-slate-700 mb-1.5">MAC Address ESP32 <span class="text-rose-500">*</span></label>
                <input type="text" name="mac_address" required value="{{ old('mac_address') }}"
                    placeholder="Contoh: 24:6F:28:BD:01:01"
                    class="w-full h-11 px-3.5 rounded-xl border border-slate-200 text-sm font-mono focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <p class="text-[11px] text-slate-400 mt-1">Dapat dilihat pada serial monitor saat ESP32 boot.</p>
            </div>

            {{-- Lokasi / Fasilitas --}}
            <div>
                <label class="block text-xs font-bold font-mono uppercase tracking-wider text-slate-700 mb-1.5">Lokasi / Fasilitas Pabrik <span class="text-rose-500">*</span></label>
                <input type="text" name="location" required value="{{ old('location') }}"
                    placeholder="Contoh: Pabrik Bandung - Area Sterilisasi (Lt. 1)"
                    class="w-full h-11 px-3.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                {{-- Divisi --}}
                <div>
                    <label class="block text-xs font-bold font-mono uppercase tracking-wider text-slate-700 mb-1.5">Divisi Penempatan</label>
                    <select name="division_id" class="w-full h-11 px-3 rounded-xl border border-slate-200 text-sm bg-white focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua / Umum</option>
                        @foreach ($divisions as $div)
                            <option value="{{ $div->division_id }}" {{ old('division_id') === $div->division_id ? 'selected' : '' }}>
                                {{ $div->division_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-xs font-bold font-mono uppercase tracking-wider text-slate-700 mb-1.5">Status Awal</label>
                    <select name="status" class="w-full h-11 px-3 rounded-xl border border-slate-200 text-sm bg-white focus:ring-2 focus:ring-blue-500">
                        <option value="online" selected>Online</option>
                        <option value="offline">Offline</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                </div>
            </div>

            {{-- Tanggal Pasang --}}
            <div>
                <label class="block text-xs font-bold font-mono uppercase tracking-wider text-slate-700 mb-1.5">Tanggal Pemasangan</label>
                <input type="date" name="installed_at" value="{{ old('installed_at', date('Y-m-d')) }}"
                    class="w-full h-11 px-3.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeCreateModal()"
                    class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition font-display">
                    Batal
                </button>
                <button type="submit"
                    class="px-5 py-2.5 rounded-xl bg-blue-700 hover:bg-blue-800 text-white text-xs font-bold shadow-md shadow-blue-700/20 transition font-display">
                    Simpan &amp; Generate Key
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ================= MODAL EDIT ALAT ================= --}}
<div id="editDeviceModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs hidden p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-6 sm:p-7 border border-slate-200 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div>
                <h3 class="font-display font-bold text-lg text-slate-900">Edit Data Alat Reader RFID</h3>
                <p class="text-xs text-slate-500 font-mono mt-0.5">Ubah nama, lokasi, divisi, atau status alat</p>
            </div>
            <button onclick="closeEditModal()" class="text-slate-400 hover:text-slate-700 p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form id="editDeviceForm" method="POST" action="" class="space-y-4 mt-5">
            @csrf
            @method('PUT')

            {{-- Nama Alat --}}
            <div>
                <label class="block text-xs font-bold font-mono uppercase tracking-wider text-slate-700 mb-1.5">Nama Alat / Reader <span class="text-rose-500">*</span></label>
                <input type="text" id="edit_device_name" name="device_name" required
                    class="w-full h-11 px-3.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            {{-- MAC Address --}}
            <div>
                <label class="block text-xs font-bold font-mono uppercase tracking-wider text-slate-700 mb-1.5">MAC Address ESP32 <span class="text-rose-500">*</span></label>
                <input type="text" id="edit_mac_address" name="mac_address" required
                    class="w-full h-11 px-3.5 rounded-xl border border-slate-200 text-sm font-mono focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            {{-- Lokasi / Fasilitas --}}
            <div>
                <label class="block text-xs font-bold font-mono uppercase tracking-wider text-slate-700 mb-1.5">Lokasi / Fasilitas Pabrik <span class="text-rose-500">*</span></label>
                <input type="text" id="edit_location" name="location" required
                    class="w-full h-11 px-3.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                {{-- Divisi --}}
                <div>
                    <label class="block text-xs font-bold font-mono uppercase tracking-wider text-slate-700 mb-1.5">Divisi Penempatan</label>
                    <select id="edit_division_id" name="division_id" class="w-full h-11 px-3 rounded-xl border border-slate-200 text-sm bg-white focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua / Umum</option>
                        @foreach ($divisions as $div)
                            <option value="{{ $div->division_id }}">{{ $div->division_name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-xs font-bold font-mono uppercase tracking-wider text-slate-700 mb-1.5">Status Alat</label>
                    <select id="edit_status" name="status" class="w-full h-11 px-3 rounded-xl border border-slate-200 text-sm bg-white focus:ring-2 focus:ring-blue-500">
                        <option value="online">Online</option>
                        <option value="offline">Offline</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                </div>
            </div>

            {{-- Tanggal Pasang --}}
            <div>
                <label class="block text-xs font-bold font-mono uppercase tracking-wider text-slate-700 mb-1.5">Tanggal Pemasangan</label>
                <input type="date" id="edit_installed_at" name="installed_at"
                    class="w-full h-11 px-3.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeEditModal()"
                    class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition font-display">
                    Batal
                </button>
                <button type="submit"
                    class="px-5 py-2.5 rounded-xl bg-blue-700 hover:bg-blue-800 text-white text-xs font-bold shadow-md shadow-blue-700/20 transition font-display">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openCreateModal() {
        document.getElementById('createDeviceModal').classList.remove('hidden');
    }
    function closeCreateModal() {
        document.getElementById('createDeviceModal').classList.add('hidden');
    }

    function openEditModal(device) {
        document.getElementById('editDeviceForm').action = `/admin/devices/${device.device_id}`;
        document.getElementById('edit_device_name').value = device.device_name || '';
        document.getElementById('edit_mac_address').value = device.mac_address || '';
        document.getElementById('edit_location').value = device.location || '';
        document.getElementById('edit_division_id').value = device.division_id || '';
        document.getElementById('edit_status').value = device.status || 'online';
        document.getElementById('edit_installed_at').value = device.installed_at ? device.installed_at.substring(0, 10) : '';

        document.getElementById('editDeviceModal').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('editDeviceModal').classList.add('hidden');
    }

    function copyApiKey() {
        const text = document.getElementById('apiKeyText').innerText;
        navigator.clipboard.writeText(text).then(() => {
            const btn = document.getElementById('copyBtnText');
            btn.innerText = 'Tersalin!';
            setTimeout(() => { btn.innerText = 'Salin API Key'; }, 2000);
        });
    }
</script>
@endpush

@endsection
