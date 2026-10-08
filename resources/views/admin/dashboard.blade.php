@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Pusat Kontrol Utama')

@section('content')
<div class="space-y-6">

    {{-- ================= HERO WELCOME BANNER (ROYAL / OCEAN BLUE THEME) ================= --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-700 via-blue-600 to-indigo-700 p-6 sm:p-8 text-white shadow-lg shadow-blue-700/15 border border-blue-500/30">
        {{-- Decorative Glow Circles --}}
        <div class="absolute -top-24 -right-24 w-72 h-72 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -bottom-20 -left-20 w-60 h-60 bg-cyan-400/20 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="max-w-2xl space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 border border-white/25 text-white text-xs font-mono backdrop-blur-xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-300 animate-pulse"></span>
                    Sistem RFID Multi-Lokasi Aktif &bull; Terkoneksi
                </div>
                <h1 class="font-display font-bold text-2xl sm:text-3xl text-white tracking-tight">
                    Selamat Datang, {{ auth()->user()->full_name }}
                </h1>
                <p class="text-sm text-blue-100 leading-relaxed">
                    Sistem pemantauan siklus sterilisasi autoclave, keausan tag RFID, dan kesiapan garment cleanroom lintas fasilitas pabrik secara terpadu.
                </p>
            </div>

            {{-- Quick Action Shortcuts --}}
            <div class="flex flex-wrap gap-2.5 shrink-0">
                <a href="{{ route('admin.garment-config.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-white text-blue-700 hover:bg-blue-50 transition shadow-sm font-sans">
                    <svg class="w-4 h-4 text-blue-700" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Input Garment
                </a>
                <a href="{{ route('admin.pindah-tag.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold bg-white/15 text-white hover:bg-white/25 transition border border-white/20 backdrop-blur-xs font-sans">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    Pindah Tag
                </a>
                <a href="{{ route('admin.alerts.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-rose-500 text-white hover:bg-rose-600 transition shadow-sm font-sans">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    Alerts
                </a>
            </div>
        </div>
    </div>

    {{-- ================= 5 KPI STAT CARDS ================= --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">

        {{-- 1. Garment Aktif --}}
        <div class="card card-hover p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 font-mono">Garment Aktif</span>
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <p class="font-display font-extrabold text-2xl sm:text-3xl text-slate-900">
                    {{ number_format($kpi['garment_aktif']) }}
                </p>
                <p class="text-[11px] text-blue-700 font-mono mt-1 flex items-center gap-1 font-medium">
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-blue-600"></span> Siap &amp; Beroperasi
                </p>
            </div>
        </div>

        {{-- 2. Tag Aktif --}}
        <div class="card card-hover p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 font-mono">Tag RFID</span>
                <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <p class="font-display font-extrabold text-2xl sm:text-3xl text-slate-900">
                    {{ number_format($kpi['tag_aktif']) }}
                </p>
                <p class="text-[11px] text-indigo-700 font-mono mt-1 flex items-center gap-1 font-medium">
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-indigo-600"></span> Tag Terpasang
                </p>
            </div>
        </div>

        {{-- 3. Item Kritis --}}
        <div class="card card-hover p-5 flex flex-col justify-between {{ $kpi['item_kritis'] > 0 ? 'bg-rose-50/70 border-rose-200' : '' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider {{ $kpi['item_kritis'] > 0 ? 'text-rose-700' : 'text-slate-500' }} font-mono">
                    Item Kritis (&ge;90%)
                </span>
                <div class="w-9 h-9 rounded-xl {{ $kpi['item_kritis'] > 0 ? 'bg-rose-100 text-rose-600' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center border border-rose-200">
                    <svg class="w-5 h-5 {{ $kpi['item_kritis'] > 0 ? 'animate-pulse' : '' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M12 8v4m0 4h.01" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <p class="font-display font-extrabold text-2xl sm:text-3xl {{ $kpi['item_kritis'] > 0 ? 'text-rose-600' : 'text-slate-900' }}">
                    {{ number_format($kpi['item_kritis']) }}
                </p>
                <p class="text-[11px] font-mono mt-1 {{ $kpi['item_kritis'] > 0 ? 'text-rose-700 font-bold' : 'text-slate-500' }}">
                    {{ $kpi['item_kritis'] > 0 ? 'Perlu Penggantian' : 'Semua Siklus Aman' }}
                </p>
            </div>
        </div>

        {{-- 4. Scan Hari Ini --}}
        <div class="card card-hover p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 font-mono">Scan Hari Ini</span>
                <div class="w-9 h-9 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center border border-teal-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <p class="font-display font-extrabold text-2xl sm:text-3xl text-teal-700">
                    {{ number_format($kpi['scan_hari_ini']) }}
                </p>
                <p class="text-[11px] text-teal-700 font-mono mt-1 flex items-center gap-1 font-medium">
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-teal-500"></span> Pembacaan Sukses
                </p>
            </div>
        </div>

        {{-- 5. Device Online --}}
        <div class="card card-hover p-5 flex flex-col justify-between col-span-2 lg:col-span-1">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 font-mono">Reader Status</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <rect x="4" y="4" width="16" height="16" rx="2" />
                        <rect x="9" y="9" width="6" height="6" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <p class="font-display font-extrabold text-2xl sm:text-3xl text-slate-900">
                    {{ $kpi['device_online'] }} <span class="text-base font-normal text-slate-400">/ {{ $kpi['device_total'] }}</span>
                </p>
                <p class="text-[11px] text-emerald-700 font-mono mt-1 flex items-center gap-1 font-bold">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Multi-Plant Online
                </p>
            </div>
        </div>
    </div>

    {{-- ================= MULTI-FACILITY STATUS WIDGET ================= --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- Pabrik Bandung --}}
        <div class="card p-5 border-l-4 border-l-blue-600 shadow-xs">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-800 flex items-center justify-center font-bold text-xs">
                        BDG
                    </div>
                    <div>
                        <h3 class="font-display font-bold text-sm text-slate-900">Fasilitas Pabrik Bandung</h3>
                        <p class="text-xs text-slate-500">Area Produksi &amp; Sterilisasi Utama</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-mono font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Online
                </span>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 grid grid-cols-2 gap-2 text-xs font-mono text-slate-600">
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase">Reader Autoclave:</span>
                    <span class="font-semibold text-slate-800">Line 1 (Lt. 1) &bull; Aktif</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase">Reader Gowning:</span>
                    <span class="font-semibold text-slate-800">Ruang Antara &bull; Aktif</span>
                </div>
            </div>
        </div>

        {{-- Pabrik Surabaya --}}
        <div class="card p-5 border-l-4 border-l-emerald-600 shadow-xs">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-xs">
                        SBY
                    </div>
                    <div>
                        <h3 class="font-display font-bold text-sm text-slate-900">Fasilitas Pabrik Surabaya</h3>
                        <p class="text-xs text-slate-500">Area Formulasi &amp; Sterilisasi Sentral</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-mono font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Online
                </span>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 grid grid-cols-2 gap-2 text-xs font-mono text-slate-600">
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase">Reader Autoclave:</span>
                    <span class="font-semibold text-slate-800">Line 2 &bull; Aktif</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase">Reader Gowning:</span>
                    <span class="font-semibold text-slate-800">Koridor Utama &bull; Aktif</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= AKTIVITAS SCAN TERBARU ================= --}}
    <div class="card p-6 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-6">
            <div>
                <h3 class="font-display font-bold text-lg text-slate-900">Aktivitas Scan Sterilisasi Realtime</h3>
                <p class="text-xs text-slate-500 font-mono mt-0.5">Rantai audit pelacakan sterilisasi lintas fasilitas</p>
            </div>
            <a href="{{ route('admin.scan-events.index') }}"
                class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-700 hover:text-blue-900 font-mono transition">
                Lihat Seluruh Histori Scan &rarr;
            </a>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse ($recentScans ?? [] as $scan)
                @php
                    $isBandung = str_contains($scan->location ?? '', 'Bandung');
                    $eventBadge = match ($scan->event_type) {
                        'pre_autoclave' => [
                            'bg' => 'bg-blue-50 text-blue-800 border-blue-200',
                            'label' => 'Pre-Autoclave',
                            'dot' => 'bg-blue-600',
                        ],
                        'post_autoclave' => [
                            'bg' => 'bg-teal-50 text-teal-800 border-teal-200',
                            'label' => 'Post-Autoclave (Selesai)',
                            'dot' => 'bg-teal-600',
                        ],
                        'usage_checkpoint' => [
                            'bg' => 'bg-amber-50 text-amber-800 border-amber-200',
                            'label' => 'Usage Checkpoint',
                            'dot' => 'bg-amber-500',
                        ],
                        default => [
                            'bg' => 'bg-slate-100 text-slate-700 border-slate-200',
                            'label' => ucfirst(str_replace('_', ' ', $scan->event_type)),
                            'dot' => 'bg-slate-500',
                        ],
                    };
                @endphp

                <div class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-blue-50/40 rounded-xl px-3 -mx-3 transition">
                    <div class="flex items-start sm:items-center gap-3">
                        <div class="w-3 h-3 rounded-full {{ $eventBadge['dot'] }} {{ $loop->first ? 'ring-4 ring-blue-100 animate-pulse' : '' }} mt-1 sm:mt-0 shrink-0"></div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-bold text-slate-900 text-sm">
                                    {{ $scan->garment->garment_code ?? 'Garment' }}
                                </span>
                                <span class="text-xs text-slate-400 font-mono">
                                    Tag: {{ $scan->tag->tag_uid ?? '—' }}
                                </span>
                                {{-- Location badge --}}
                                @if($isBandung)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                        Bandung
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Surabaya
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-500 font-mono mt-0.5">
                                {{ $scan->location ?? $scan->device->device_name }} &bull; {{ optional($scan->scan_timestamp)->diffForHumans() ?? 'Baru saja' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 self-end sm:self-auto shrink-0">
                        <span class="font-display text-xs font-bold text-slate-700">
                            Siklus #{{ $scan->cycle_count_after ?? 0 }}
                        </span>
                        <span class="px-2.5 py-1 rounded-full text-xs font-mono font-semibold border {{ $eventBadge['bg'] }}">
                            {{ $eventBadge['label'] }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 text-slate-400 font-mono text-xs">
                    Belum ada aktivitas scan terbaru yang tercatat.
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
