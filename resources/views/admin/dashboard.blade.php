@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

    <style>
        /* CSS mandiri untuk bar chart "Statistik Garment Mingguan" — sengaja
                                       di-scope di sini (bukan andalkan stylesheet global admin) supaya
                                       tidak bergantung pada class .bars/.bar-col/.bar yang mungkin
                                       belum didefinisikan lengkap di file CSS utama. */
        .bars {
            display: flex;
            align-items: flex-end;
            gap: 14px;
            height: 220px;
            padding: 10px 4px 0 4px;
        }

        .bar-col {
            flex: 1;
            min-width: 0;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
        }

        .bar-val {
            font-size: 12px;
            font-weight: 700;
            color: var(--ink-soft, #64748b);
            margin-bottom: 6px;
            white-space: nowrap;
        }

        .bar {
            width: 100%;
            max-width: 34px;
            background: linear-gradient(180deg, var(--navy, #0f1f4b) 0%, var(--navy, #0f1f4b) 100%);
            border-radius: 5px 5px 0 0;
            min-height: 4px;
        }

        .bar-label {
            margin-top: 8px;
            font-size: 12px;
            color: var(--ink-soft, #64748b);
            font-weight: 500;
        }
    </style>

    <div class="main">

        <div class="content">

            {{-- KPI Cards --}}
            <div
                style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:16px; margin-bottom:20px;">

                <div class="card" style="padding:16px;">
                    <p class="mono"
                        style="font-size:11px; text-transform:uppercase; letter-spacing:0.05em; color:var(--ink-soft); margin:0 0 6px 0;">
                        Garment Aktif
                    </p>
                    <p class="font-display" style="font-size:26px; font-weight:700; margin:0; color:var(--navy);">
                        {{ number_format($kpi['garment_aktif']) }}
                    </p>
                </div>

                <div class="card" style="padding:16px;">
                    <p class="mono"
                        style="font-size:11px; text-transform:uppercase; letter-spacing:0.05em; color:var(--ink-soft); margin:0 0 6px 0;">
                        Tag Aktif
                    </p>
                    <p class="font-display" style="font-size:26px; font-weight:700; margin:0; color:var(--navy);">
                        {{ number_format($kpi['tag_aktif']) }}
                    </p>
                </div>

                <div class="card"
                    style="padding:16px; {{ $kpi['item_kritis'] > 0 ? 'background:var(--red-soft);' : '' }}">
                    <p class="mono"
                        style="font-size:11px; text-transform:uppercase; letter-spacing:0.05em; color:{{ $kpi['item_kritis'] > 0 ? 'var(--red)' : 'var(--ink-soft)' }}; margin:0 0 6px 0;">
                        Item Kritis (&ge;90%)
                    </p>
                    <p class="font-display"
                        style="font-size:26px; font-weight:700; margin:0; color:{{ $kpi['item_kritis'] > 0 ? 'var(--red)' : 'var(--navy)' }};">
                        {{ number_format($kpi['item_kritis']) }}
                    </p>
                </div>

                <div class="card" style="padding:16px;">
                    <p class="mono"
                        style="font-size:11px; text-transform:uppercase; letter-spacing:0.05em; color:var(--ink-soft); margin:0 0 6px 0;">
                        Scan Hari Ini
                    </p>
                    <p class="font-display" style="font-size:26px; font-weight:700; margin:0; color:var(--teal);">
                        {{ number_format($kpi['scan_hari_ini']) }}
                    </p>
                </div>

                <div class="card" style="padding:16px;">
                    <p class="mono"
                        style="font-size:11px; text-transform:uppercase; letter-spacing:0.05em; color:var(--ink-soft); margin:0 0 6px 0;">
                        Device Online
                    </p>
                    <p class="font-display"
                        style="font-size:26px; font-weight:700; margin:0; color:{{ $kpi['device_online'] < $kpi['device_total'] ? 'var(--copper)' : 'var(--teal)' }};">
                        {{ $kpi['device_online'] }} <span style="font-size:14px; font-weight:400; color:var(--ink-soft);">/
                            {{ $kpi['device_total'] }}</span>
                    </p>
                </div>

            </div>

            {{-- Grafik --}}


            {{-- Aktivitas Scan Terbaru --}}
            {{-- Aktivitas Scan Terbaru --}}
            <style>
                .scan-timeline {
                    position: relative;
                    padding-left: 24px;
                }

                .scan-timeline::before {
                    content: '';
                    position: absolute;
                    left: 5px;
                    top: 8px;
                    bottom: 8px;
                    width: 2px;
                    background: #e6e9f0;
                }

                .scan-row {
                    position: relative;
                    padding: 11px 0;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    gap: 14px;
                }

                .scan-row+.scan-row {
                    border-top: 1px solid #f1f3f7;
                }

                .scan-dot {
                    position: absolute;
                    left: -24px;
                    top: 50%;
                    width: 12px;
                    height: 12px;
                    border-radius: 50%;
                    transform: translateY(-50%);
                    box-shadow: 0 0 0 3px #fff;
                }

                .scan-dot--pulse::after {
                    content: '';
                    position: absolute;
                    inset: -4px;
                    border-radius: 50%;
                    border: 1px solid currentColor;
                    opacity: 0.5;
                    animation: scanPulse 1.8s ease-out infinite;
                }

                @keyframes scanPulse {
                    0% {
                        transform: scale(0.7);
                        opacity: 0.6;
                    }

                    100% {
                        transform: scale(1.8);
                        opacity: 0;
                    }
                }

                @media (prefers-reduced-motion: reduce) {
                    .scan-dot--pulse::after {
                        animation: none;
                    }
                }
            </style>

            <div class="card" style="padding:20px; margin-bottom:20px;">
                <div class="row" style="justify-content:space-between; align-items:baseline; margin-bottom:4px;">
                    <h3 class="font-display" style="margin:0; font-size:16px; font-weight:600;">
                        Aktivitas Scan Terbaru
                    </h3>
                    <span class="mono" style="font-size:11px; color:var(--ink-soft);">
                        {{ count($recentScans ?? []) }} aktivitas
                    </span>
                </div>
                <p class="mono" style="margin:0 0 16px; font-size:11px; color:var(--ink-soft);">
                    Rantai pengawasan siklus sterilisasi, dari yang terbaru
                </p>

                @forelse ($recentScans ?? [] as $scan)
                    @php
                        $eventStyle = match ($scan->event_type) {
                            'pre_autoclave' => [
                                'bg' => 'var(--navy-soft)',
                                'fg' => 'var(--navy)',
                                'label' => 'Pre-Autoclave',
                            ],
                            'post_autoclave' => [
                                'bg' => 'var(--teal-soft)',
                                'fg' => 'var(--teal)',
                                'label' => 'Post-Autoclave',
                            ],
                            'usage_checkpoint' => [
                                'bg' => 'var(--copper-soft)',
                                'fg' => 'var(--copper)',
                                'label' => 'Usage Checkpoint',
                            ],
                            'manual_reentry' => [
                                'bg' => 'var(--red-soft)',
                                'fg' => 'var(--red)',
                                'label' => 'Manual Re-entry',
                            ],
                            default => [
                                'bg' => 'var(--ink-soft)',
                                'fg' => 'var(--ink)',
                                'label' => ucfirst(str_replace('_', ' ', $scan->event_type)),
                            ],
                        };
                    @endphp

                    <div class="scan-timeline" style="{{ $loop->last ? '' : '' }}">
                        <div class="scan-row">
                            <span class="scan-dot {{ $loop->first ? 'scan-dot--pulse' : '' }}"
                                style="background:{{ $eventStyle['fg'] }}; color:{{ $eventStyle['fg'] }};"></span>

                            <div style="display:flex; flex-direction:column; gap:2px;">
                                <p class="item-title" style="margin:0;">
                                    {{ $scan->garment->garment_code ?? '—' }}
                                    <span class="mono" style="font-size:11px; color:var(--ink-soft); font-weight:400;">
                                        · Tag {{ $scan->tag->tag_uid ?? '—' }}
                                    </span>
                                </p>
                                <span class="mono" style="font-size:11px; color:var(--ink-soft);">
                                    {{ $scan->device->device_name ?? '—' }}
                                    @if ($scan->location)
                                        · {{ $scan->location }}
                                    @endif
                                    · {{ optional($scan->scan_timestamp)->format('d M Y, H:i') ?? '—' }}
                                </span>
                            </div>

                            <div style="display:flex; align-items:center; gap:10px; flex-shrink:0;">
                                <span class="font-display" style="font-size:13px; font-weight:700; color:var(--ink-soft);">
                                    Siklus {{ $scan->cycle_count_after ?? 0 }}
                                </span>
                                <span class="mono"
                                    style="font-size:11px; font-weight:600; padding:4px 10px; border-radius:999px; background:{{ $eventStyle['bg'] }}; color:{{ $eventStyle['fg'] }}; white-space:nowrap;">
                                    {{ $eventStyle['label'] }}
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div style="text-align:center; padding:24px 0;">
                        <p class="mono" style="font-size:12px; color:var(--ink-soft); margin:0;">
                            Belum ada aktivitas scan. Data akan muncul begitu perangkat mulai mengirim.
                        </p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Forecast --}}


    </div>

    </div>

@endsection

@push('scripts')
    <script>
        console.log('Dashboard Loaded');
    </script>
@endpush
