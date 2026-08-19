<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Laporan Status Baju</title>
    <style>
        @page {
            margin: 24px 30px 40px 30px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 9px;
            color: #1e293b;
        }

        .topbar {
            height: 6px;
            background-color: #0f1f4b;
            margin-bottom: 14px;
        }

        table.header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #0f1f4b;
            padding-bottom: 10px;
            margin-bottom: 16px;
        }

        table.header-table td {
            vertical-align: top;
            padding-bottom: 10px;
        }

        .title-main {
            font-size: 26px;
            font-weight: bold;
            color: #0f1f4b;
            letter-spacing: 0.5px;
            margin: 0;
            line-height: 1.15;
        }

        .title-sub {
            font-size: 15px;
            font-weight: bold;
            color: #0f1f4b;
            margin: 2px 0 4px 0;
        }

        .title-source {
            font-size: 9px;
            color: #64748b;
            letter-spacing: 1px;
        }

        table.meta-table {
            font-size: 8.5px;
            color: #0f1f4b;
        }

        table.meta-table td {
            padding: 1.5px 0;
        }

        table.meta-table td.meta-label {
            font-weight: bold;
            padding-right: 6px;
            white-space: nowrap;
        }

        table.meta-table td.meta-colon {
            padding-right: 6px;
        }

        .section-banner {
            background-color: #0f1f4b;
            color: #ffffff;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 0.5px;
            padding: 6px 10px;
            margin-bottom: 10px;
        }

        table.summary-layout {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        table.summary-layout>tr>td {
            vertical-align: top;
        }

        .stat-box {
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 8px 10px;
            margin-bottom: 6px;
        }

        .stat-box .stat-label {
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 0.3px;
        }

        .stat-box .stat-value {
            font-size: 22px;
            font-weight: bold;
            margin: 4px 0 0 0;
        }

        .stat-box .stat-unit {
            font-size: 8px;
            color: #64748b;
        }

        .stat-kritis {
            border-color: #fecaca;
            background: #fef2f2;
        }

        .stat-kritis .stat-label,
        .stat-kritis .stat-value {
            color: #dc2626;
        }

        .stat-waspada {
            border-color: #fde68a;
            background: #fffbeb;
        }

        .stat-waspada .stat-label,
        .stat-waspada .stat-value {
            color: #d97706;
        }

        .stat-normal {
            border-color: #bbf7d0;
            background: #f0fdf4;
        }

        .stat-normal .stat-label,
        .stat-normal .stat-value {
            color: #16a34a;
        }

        table.donut-legend-table {
            font-size: 8.5px;
            color: #334155;
        }

        table.donut-legend-table td {
            padding: 2px 0;
        }

        .legend-dot {
            display: inline-block;
            width: 7px;
            height: 7px;
            border-radius: 50%;
            margin-right: 5px;
        }

        .total-label {
            font-size: 8px;
            color: #64748b;
            letter-spacing: 0.5px;
            margin-top: 8px;
        }

        .total-value {
            font-size: 16px;
            font-weight: bold;
            color: #0f1f4b;
        }

        table.detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        table.detail-table thead th {
            background-color: #0f1f4b;
            color: #ffffff;
            font-size: 7.5px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 6px 5px;
            text-align: center;
            border: 1px solid #1e3a6f;
        }

        table.detail-table tbody td {
            font-size: 8.5px;
            padding: 6px 5px;
            border: 1px solid #e2e8f0;
            text-align: center;
            vertical-align: middle;
        }

        table.detail-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .cell-strong {
            font-weight: bold;
            color: #0f1f4b;
        }

        .cell-muted {
            color: #94a3b8;
            font-style: italic;
        }

        .progress-track {
            width: 100%;
            height: 5px;
            background-color: #e2e8f0;
            border-radius: 3px;
            margin: 3px 0 2px 0;
        }

        .progress-fill {
            height: 5px;
            border-radius: 3px;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 7.5px;
            font-weight: bold;
            color: #ffffff;
        }

        .badge-kritis {
            background-color: #dc2626;
        }

        .badge-waspada {
            background-color: #f59e0b;
        }

        .badge-normal {
            background-color: #16a34a;
        }

        table.footer-note-layout {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        table.footer-note-layout>tr>td {
            vertical-align: top;
        }

        .catatan-box {
            font-size: 8px;
            color: #475569;
            line-height: 1.6;
        }

        .catatan-box ul {
            margin: 0;
            padding-left: 12px;
        }

        .rumus-box {
            background-color: #f1f5f9;
            border-radius: 4px;
            padding: 10px 12px;
            font-size: 8px;
            color: #334155;
            line-height: 1.6;
        }

        .rumus-title {
            font-weight: bold;
            color: #0f1f4b;
            font-size: 8.5px;
            margin-bottom: 4px;
        }

        .page-footer {
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
            font-size: 7.5px;
            color: #94a3b8;
        }
    </style>
</head>

<body>

    @php
        // Field yang tersedia dari GarmentReportController@buildGarmentStatusReportData:
        //   items: garment_code, current_cycle_count, max_cycle_limit,
        //          estimated_days_remaining, estimated_days_display, pct_terpakai,
        //          category_name, division_name, size, status ('Kritis'/'Waspada'),
        //          tag_uid, total_cycles_used, rated_max_cycles, tag_pct_terpakai
        //   total_kritis, total_waspada, total_normal, total_garment_aktif
        //   periode_awal, periode_akhir, generated_at

        $totalItem = $total_garment_aktif ?? $total_kritis + $total_waspada + $total_normal;
        $pctKritis = $totalItem > 0 ? round(($total_kritis / $totalItem) * 100, 1) : 0;
        $pctWaspada = $totalItem > 0 ? round(($total_waspada / $totalItem) * 100, 1) : 0;
        $pctNormal = $totalItem > 0 ? round(($total_normal / $totalItem) * 100, 1) : 0;

        $circumference = 2 * M_PI * 40;
        $lenKritis = $circumference * ($totalItem > 0 ? $total_kritis / $totalItem : 0);
        $lenWaspada = $circumference * ($totalItem > 0 ? $total_waspada / $totalItem : 0);
        $lenNormal = $circumference * ($totalItem > 0 ? $total_normal / $totalItem : 0);
    @endphp

    <div class="topbar"></div>

    {{-- ================= HEADER ================= --}}
    <table class="header-table">
        <tr>
            <td style="width: 58%;">
                <p class="title-main">LAPORAN STATUS BAJU</p>
                <p class="title-sub">WASPADA &amp; KRITIS</p>
                <p class="title-source">SISTEM ALFARFID</p>
            </td>
            <td style="width: 42%;">
                <table class="meta-table">
                    <tr>
                        <td class="meta-label">PERIODE</td>
                        <td class="meta-colon">:</td>
                        <td>
                            {{ $periode_awal->translatedFormat('d F Y') }}
                            &ndash;
                            {{ $periode_akhir->translatedFormat('d F Y') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="meta-label">TANGGAL GENERATE</td>
                        <td class="meta-colon">:</td>
                        <td>{{ $generated_at->translatedFormat('d F Y, H:i') }} WIB</td>
                    </tr>
                    <tr>
                        <td class="meta-label">SUMBER DATA</td>
                        <td class="meta-colon">:</td>
                        <td>Sistem AlfarFID</td>
                    </tr>
                    <tr>
                        <td class="meta-label">DIBUAT OLEH</td>
                        <td class="meta-colon">:</td>
                        <td>{{ auth()->user()->full_name ?? 'Sistem Otomatis' }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">DOKUMEN</td>
                        <td class="meta-colon">:</td>
                        <td>Internal Use Only</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- ================= RINGKASAN STATUS ================= --}}
    <div class="section-banner">RINGKASAN STATUS</div>

    <table class="summary-layout">
        <tr>
            <td style="width: 40%;">
                <div class="stat-box stat-kritis">
                    <div class="stat-label">KRITIS (&le; 7 HARI)</div>
                    <div class="stat-value">{{ $total_kritis }} <span class="stat-unit">item</span></div>
                </div>
                <div class="stat-box stat-waspada">
                    <div class="stat-label">WASPADA (8&ndash;14 HARI)</div>
                    <div class="stat-value">{{ $total_waspada }} <span class="stat-unit">item</span></div>
                </div>
                <div class="stat-box stat-normal">
                    <div class="stat-label">NORMAL (&gt; 14 HARI)</div>
                    <div class="stat-value">{{ $total_normal }} <span class="stat-unit">item</span></div>
                </div>
            </td>
            <td style="width: 60%; padding-left: 14px;">
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 45%; text-align: center;">
                            <svg width="140" height="140" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="40" fill="none" stroke="#e2e8f0"
                                    stroke-width="14" />
                                @if ($totalItem > 0)
                                    <circle cx="50" cy="50" r="40" fill="none" stroke="#16a34a"
                                        stroke-width="14" stroke-dasharray="{{ $lenNormal }} {{ $circumference }}"
                                        stroke-dashoffset="0" transform="rotate(-90 50 50)" />
                                    <circle cx="50" cy="50" r="40" fill="none" stroke="#f59e0b"
                                        stroke-width="14" stroke-dasharray="{{ $lenWaspada }} {{ $circumference }}"
                                        stroke-dashoffset="{{ -$lenNormal }}" transform="rotate(-90 50 50)" />
                                    <circle cx="50" cy="50" r="40" fill="none" stroke="#dc2626"
                                        stroke-width="14" stroke-dasharray="{{ $lenKritis }} {{ $circumference }}"
                                        stroke-dashoffset="{{ -($lenNormal + $lenWaspada) }}"
                                        transform="rotate(-90 50 50)" />
                                @endif
                            </svg>
                        </td>
                        <td style="width: 55%;">
                            <table class="donut-legend-table">
                                <tr>
                                    <td><span class="legend-dot" style="background:#dc2626;"></span>Kritis</td>
                                    <td style="text-align:right;">{{ $total_kritis }} ({{ $pctKritis }}%)</td>
                                </tr>
                                <tr>
                                    <td><span class="legend-dot" style="background:#f59e0b;"></span>Waspada</td>
                                    <td style="text-align:right;">{{ $total_waspada }} ({{ $pctWaspada }}%)</td>
                                </tr>
                                <tr>
                                    <td><span class="legend-dot" style="background:#16a34a;"></span>Normal</td>
                                    <td style="text-align:right;">{{ $total_normal }} ({{ $pctNormal }}%)</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <div class="total-label">TOTAL</div>
                <div class="total-value">{{ $totalItem }} item</div>
            </td>
        </tr>
    </table>

    {{-- ================= DETAIL STATUS BAJU ================= --}}
    <div class="section-banner">DETAIL STATUS BAJU (WASPADA &amp; KRITIS)</div>

    <table class="detail-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 8%;">Kode</th>
                <th rowspan="2" style="width: 13%;">Kategori</th>
                <th rowspan="2" style="width: 13%;">Divisi</th>
                <th colspan="2">RFID Tag</th>
                <th colspan="2">Sterile Garment</th>
                <th rowspan="2" style="width: 12%;">Estimasi Mencapai Batas</th>
                <th rowspan="2" style="width: 12%;">Status</th>
            </tr>
            <tr>
                <th style="width: 10%;">Cycle</th>
                <th style="width: 10%;">Utilisasi</th>
                <th style="width: 10%;">Cycle</th>
                <th style="width: 10%;">Utilisasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $item)
                @php
                    $garmentColor =
                        $item->pct_terpakai >= 90 ? '#dc2626' : ($item->pct_terpakai >= 70 ? '#f59e0b' : '#16a34a');
                    $tagColor =
                        $item->tag_pct_terpakai !== null
                            ? ($item->tag_pct_terpakai >= 90
                                ? '#dc2626'
                                : ($item->tag_pct_terpakai >= 70
                                    ? '#f59e0b'
                                    : '#16a34a'))
                            : null;
                    $badgeClass = $item->status === 'Kritis' ? 'badge-kritis' : 'badge-waspada';
                @endphp
                <tr>
                    <td class="cell-strong">{{ $item->garment_code }}</td>
                    <td>{{ $item->category_name ?? '-' }}</td>
                    <td>{{ $item->division_name ?? '-' }}</td>

                    @if ($item->tag_uid)
                        <td class="cell-strong">{{ $item->total_cycles_used }} / {{ $item->rated_max_cycles }}</td>
                        <td>
                            <div class="progress-track">
                                <div class="progress-fill"
                                    style="width:{{ min(100, $item->tag_pct_terpakai) }}%; background-color:{{ $tagColor }};">
                                </div>
                            </div>
                            {{ $item->tag_pct_terpakai }}%
                        </td>
                    @else
                        <td class="cell-muted" colspan="2">Belum ada tag</td>
                    @endif

                    <td class="cell-strong">{{ $item->current_cycle_count }} / {{ $item->max_cycle_limit }}</td>
                    <td>
                        <div class="progress-track">
                            <div class="progress-fill"
                                style="width:{{ min(100, $item->pct_terpakai) }}%; background-color:{{ $garmentColor }};">
                            </div>
                        </div>
                        {{ $item->pct_terpakai }}%
                    </td>

                    <td>{{ $item->estimated_days_display }} hari lagi</td>
                    <td><span class="badge {{ $badgeClass }}">{{ strtoupper($item->status) }}</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="padding: 14px; color:#94a3b8; font-style: italic;">
                        Tidak ada baju berstatus Waspada/Kritis untuk periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ================= CATATAN & RUMUS ================= --}}
    <table class="footer-note-layout">
        <tr>
            <td style="width: 55%; padding-right: 14px;">
                <div style="font-weight:bold; color:#0f1f4b; font-size:9px; margin-bottom:6px;">CATATAN</div>
                <div class="catatan-box">
                    <ul>
                        <li>Baju berstatus Normal (estimasi &gt; 14 hari) tidak ditampilkan per-baris, hanya dihitung di
                            ringkasan.</li>
                        <li>Kolom RFID Tag menampilkan "Belum ada tag" untuk baju yang belum dipasangi tag.</li>
                        <li>Status baris ditentukan dari estimasi keausan Sterile Garment, bukan dari kolom Tag.</li>
                    </ul>
                </div>
            </td>
            <td style="width: 45%;">
                <div class="rumus-box">
                    <div class="rumus-title">RUMUS ESTIMASI</div>
                    Sisa Cycle = Batas Maksimum &ndash; Cycle Saat Ini<br>
                    Estimasi Hari = Sisa Cycle / Rata-rata Cycle per Hari
                </div>
            </td>
        </tr>
    </table>

    {{-- ================= PAGE FOOTER ================= --}}
    <table class="page-footer" style="width: 100%;">
        <tr>
            <td style="text-align: left;">Laporan ini dihasilkan otomatis oleh Sistem AlfarFID</td>
            <td style="text-align: right;">Halaman 1 dari 1</td>
        </tr>
    </table>

</body>

</html>
