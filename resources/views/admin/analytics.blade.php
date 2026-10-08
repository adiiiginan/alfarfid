@extends('layouts.admin') {{-- sesuaikan kalau nama layout admin kamu beda --}}

@section('title', 'Analytics & Grafik')

@section('content')
    <div class="space-y-6">

        {{-- ================= HEADER ================= --}}
        <div class="flex items-start justify-between flex-wrap gap-3 mb-6">
            <div>
                <h1 class="font-display font-semibold text-2xl text-slate-800">Analytics &amp; Grafik</h1>
                <p class="text-sm text-slate-500 mt-1">
                    Ringkasan tren scan, distribusi umur pakai baju &amp; keausan tag (dua siklus terpisah), dan prediksi
                    waktu
                    retired.
                </p>
            </div>

            <a href="{{ route('admin.analytics.report.download') }}"
                class="shrink-0 inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium bg-blue-600 text-white hover:bg-blue-700 shadow-sm transition">
                <span aria-hidden="true">📄</span>
                <span>Download Laporan PDF</span>
                <span class="text-[10px] text-blue-100 font-normal">(Waspada &amp; Kritis)</span>
            </a>
        </div>

        {{-- ================= RINGKASAN ANGKA ================= --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

            <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
                <p class="text-[10px] font-mono uppercase tracking-widest text-slate-400 mb-1">
                    Total Scan ({{ $range }} hari)
                </p>
                <p class="font-display font-bold text-2xl text-slate-800">
                    {{ number_format($summary['total_scan_range']) }}
                </p>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
                <p class="text-[10px] font-mono uppercase tracking-widest text-slate-400 mb-1">Item Aktif Dipantau</p>
                <p class="font-display font-bold text-2xl text-slate-800">
                    {{ number_format($summary['total_garment_aktif']) }}
                </p>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
                <p class="text-[10px] font-mono uppercase tracking-widest text-slate-400 mb-1">Akan Retired &le; 14 Hari</p>
                <p class="font-display font-bold text-2xl text-yellow-500">
                    {{ $summary['akan_retired_14hari'] }}
                </p>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
                <p class="text-[10px] font-mono uppercase tracking-widest text-slate-400 mb-1">Akan Retired &le; 7 Hari</p>
                <p class="font-display font-bold text-2xl text-[var(--red)]">
                    {{ $summary['akan_retired_7hari'] }}
                </p>
            </div>

        </div>

        {{--
            PERUBAHAN: kartu ringkasan khusus TAG. Baju (max_cycle_limit,
            default 50) dan tag (rated_max_cycles, default 200) punya umur
            pakai berbeda dan terpisah — satu tag bisa dipindah lintas
            beberapa baju lewat "Pindah Tag", jadi tag butuh dipantau
            sendiri, bukan cuma ikut status baju yang sedang menempel.
        --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
            <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
                <p class="text-[10px] font-mono uppercase tracking-widest text-slate-400 mb-1">Tag Aktif Dipantau</p>
                <p class="font-display font-bold text-2xl text-slate-800">
                    {{ number_format($summary['total_tag_aktif']) }}
                </p>
            </div>
            <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
                <p class="text-[10px] font-mono uppercase tracking-widest text-slate-400 mb-1">Tag Kritis (&ge;90% siklus)
                </p>
                <p class="font-display font-bold text-2xl text-[var(--red)]">
                    {{ $summary['tag_kritis'] }}
                </p>
            </div>
        </div>

        {{-- ================= CHART 1: TREN SCAN ================= --}}
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm mb-6">
            <div class="flex items-center justify-between flex-wrap gap-3 mb-1">
                <div>
                    <h2 class="font-display font-semibold text-slate-800">Tren Jumlah Scan</h2>
                    <p class="text-sm text-slate-500">
                        Jumlah baju yang melewati proses autoclave (pre &amp; post) dari waktu ke waktu.
                    </p>
                </div>

                <div class="flex gap-2">
                    <a href="{{ request()->fullUrlWithQuery(['period' => 'daily']) }}"
                        class="px-3 py-1.5 rounded-lg text-xs font-mono uppercase tracking-wide border
                    {{ $period === 'daily'
                        ? 'bg-[var(--copper)] border-[var(--copper)] text-white'
                        : 'border-slate-200 text-slate-600' }}">
                        Harian
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['period' => 'weekly']) }}"
                        class="px-3 py-1.5 rounded-lg text-xs font-mono uppercase tracking-wide border
                    {{ $period === 'weekly'
                        ? 'bg-[var(--copper)] border-[var(--copper)] text-white'
                        : 'border-slate-200 text-slate-600' }}">
                        Mingguan
                    </a>
                </div>
            </div>

            <canvas id="scanTrendChart" height="90" class="mt-4"></canvas>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

            {{-- ================= CHART 2: DISTRIBUSI UMUR PAKAI ================= --}}
            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm lg:col-span-2">
                <h2 class="font-display font-semibold text-slate-800">Distribusi Umur Pakai Item</h2>
                <p class="text-sm text-slate-500 mb-4">Sebaran baju aktif berdasarkan persentase siklus terpakai.</p>

                <canvas id="ageDistributionChart" height="220"></canvas>

                <div class="mt-4 space-y-1.5 text-sm">
                    <div class="flex items-center gap-2 text-slate-600">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        Aman (&lt;70%) — {{ $ageDistribution['aman'] }} item
                    </div>
                    <div class="flex items-center gap-2 text-slate-600">
                        <span class="w-2.5 h-2.5 rounded-full bg-yellow-400"></span>
                        Waspada (70-89%) — {{ $ageDistribution['waspada'] }} item
                    </div>
                    <div class="flex items-center gap-2 text-slate-600">
                        <span class="w-2.5 h-2.5 rounded-full bg-[var(--red)]"></span>
                        Kritis (&ge;90%) — {{ $ageDistribution['kritis'] }} item
                    </div>
                </div>
            </div>

            {{-- ================= CHART 3: PREDIKSI RETIRED ================= --}}
            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm lg:col-span-3">
                <h2 class="font-display font-semibold text-slate-800">Prediksi Waktu Retired</h2>
                <p class="text-sm text-slate-500 mb-4">
                    Estimasi hari tersisa sebelum baju mencapai batas maksimal siklus.
                </p>

                @if ($retirementForecast->isEmpty())
                    <p class="text-sm text-slate-500">
                        Belum ada item dengan estimasi retired dalam 30 hari ke depan.
                    </p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr
                                    class="text-left text-[10px] font-mono uppercase tracking-widest text-slate-400 border-b border-slate-200">
                                    <th class="py-2 pr-3">Kode</th>
                                    <th class="py-2 pr-3">Cycle</th>
                                    <th class="py-2 pr-3">Estimasi</th>
                                    <th class="py-2 pr-3">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($retirementForecast as $item)
                                    @php
                                        $days = (float) $item->estimated_days_remaining;
                                        $badge =
                                            $days <= 7
                                                ? ['label' => 'Kritis', 'class' => 'bg-[var(--red)] text-white']
                                                : ($days <= 14
                                                    ? ['label' => 'Waspada', 'class' => 'bg-yellow-400 text-slate-900']
                                                    : ['label' => 'Normal', 'class' => 'bg-emerald-500 text-white']);
                                    @endphp
                                    <tr class="border-b border-slate-100">
                                        <td class="py-2 pr-3 font-medium text-slate-700">{{ $item->garment_code }}</td>
                                        <td class="py-2 pr-3 text-slate-600">
                                            {{ $item->current_cycle_count }}/{{ $item->max_cycle_limit }}</td>
                                        <td class="py-2 pr-3 text-slate-600">{{ $item->estimated_days_display }} hari lagi
                                        </td>
                                        <td class="py-2 pr-3">
                                            <span
                                                class="px-2 py-0.5 rounded-full text-[11px] font-medium {{ $badge['class'] }}">
                                                {{ $badge['label'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>

        {{--
            ================= CHART 4 (BARU): KEAUSAN TAG =================
            PERUBAHAN: sengaja dipisah dari Chart 2/3 di atas (yang berbasis
            garments.max_cycle_limit) karena tag punya batas siklus sendiri
            (tags.rated_max_cycles) yang tidak identik dengan umur baju yang
            sedang menempel padanya — satu tag bisa berpindah lintas
            beberapa baju lewat "Pindah Tag".
        --}}
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 mt-6">

            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm lg:col-span-2">
                <h2 class="font-display font-semibold text-slate-800">Distribusi Keausan Tag</h2>
                <p class="text-sm text-slate-500 mb-4">Sebaran tag aktif berdasarkan persentase siklus terpakai
                    (terpisah dari umur baju).</p>

                <canvas id="tagWearChart" height="220"></canvas>

                <div class="mt-4 space-y-1.5 text-sm">
                    <div class="flex items-center gap-2 text-slate-600">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        Aman (&lt;70%) — {{ $tagWearDistribution['aman'] }} tag
                    </div>
                    <div class="flex items-center gap-2 text-slate-600">
                        <span class="w-2.5 h-2.5 rounded-full bg-yellow-400"></span>
                        Waspada (70-89%) — {{ $tagWearDistribution['waspada'] }} tag
                    </div>
                    <div class="flex items-center gap-2 text-slate-600">
                        <span class="w-2.5 h-2.5 rounded-full bg-[var(--red)]"></span>
                        Kritis (&ge;90%) — {{ $tagWearDistribution['kritis'] }} tag
                    </div>
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm lg:col-span-3">
                <h2 class="font-display font-semibold text-slate-800">Tag Paling Mendekati Batas Siklus</h2>
                <p class="text-sm text-slate-500 mb-4">
                    Diurutkan dari tag yang paling mepet ke batas <code>rated_max_cycles</code>-nya sendiri.
                </p>

                @if ($tagWearForecast->isEmpty())
                    <p class="text-sm text-slate-500">Belum ada tag aktif untuk dipantau.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr
                                    class="text-left text-[10px] font-mono uppercase tracking-widest text-slate-400 border-b border-slate-200">
                                    <th class="py-2 pr-3">Tag UID</th>
                                    <th class="py-2 pr-3">Siklus</th>
                                    <th class="py-2 pr-3">Sisa</th>
                                    <th class="py-2 pr-3">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tagWearForecast as $item)
                                    @php
                                        $pct = (float) $item->usage_percent;
                                        $badge =
                                            $pct >= 90
                                                ? ['label' => 'Kritis', 'class' => 'bg-[var(--red)] text-white']
                                                : ($pct >= 70
                                                    ? ['label' => 'Waspada', 'class' => 'bg-yellow-400 text-slate-900']
                                                    : ['label' => 'Normal', 'class' => 'bg-emerald-500 text-white']);
                                    @endphp
                                    <tr class="border-b border-slate-100">
                                        <td class="py-2 pr-3 font-mono text-xs font-medium text-slate-700">
                                            {{ $item->tag_uid }}</td>
                                        <td class="py-2 pr-3 text-slate-600">
                                            {{ $item->total_cycles_used }}/{{ $item->rated_max_cycles }}
                                            ({{ $pct }}%)
                                        </td>
                                        <td class="py-2 pr-3 text-slate-600">{{ $item->remaining_cycles }} siklus</td>
                                        <td class="py-2 pr-3">
                                            <span
                                                class="px-2 py-0.5 rounded-full text-[11px] font-medium {{ $badge['class'] }}">
                                                {{ $badge['label'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>

        {{-- ================= FR-16 & FR-17: KEPUTUSAN PENGADAAN ================= --}}
        {{-- Digabung di halaman ini (bukan menu terpisah) supaya insight dan
         keputusan actionable-nya ada di satu tempat yang sama. --}}
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm mt-6">
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h2 class="font-display font-semibold text-slate-800">Rekomendasi Pengadaan Stok Pengganti</h2>
                    <p class="text-sm text-slate-500">
                        Kelompokkan baju berdasarkan estimasi retired, lalu dapatkan rekomendasi tindakan untuk
                        procurement/CSSD.
                    </p>
                </div>
                <button type="button" onclick="toggleProcurementPanel()"
                    class="shrink-0 px-4 py-2 rounded-lg text-sm font-medium bg-[var(--copper)] text-white hover:opacity-90">
                    Generate Rekomendasi Pengadaan
                </button>
            </div>

            <div id="procurementPanel" class="hidden mt-5 border-t border-slate-100 pt-5">

                <p class="text-xs text-slate-400 mb-4">
                    Dihasilkan: {{ $procurementDecision['generated_at'] }}
                </p>

                {{-- Rekomendasi teks --}}
                <div class="space-y-2 mb-5">
                    @foreach ($procurementDecision['rekomendasi'] as $teks)
                        <div
                            class="flex items-start gap-2 text-sm text-slate-700 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2">
                            <span>💡</span>
                            <span>{{ $teks }}</span>
                        </div>
                    @endforeach
                </div>

                {{-- 3 bucket --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    <div class="border border-[var(--red)]/30 bg-[var(--red)]/5 rounded-lg p-4">
                        <p class="text-xs font-mono uppercase tracking-widest text-[var(--red)] mb-2">
                            0-7 Hari · Beli Sekarang
                        </p>
                        <p class="font-display font-bold text-2xl text-slate-800 mb-2">
                            {{ $procurementDecision['bucket_0_7']->count() }} item
                        </p>
                        <ul class="text-sm text-slate-600 space-y-1 max-h-48 overflow-y-auto">
                            @forelse($procurementDecision['bucket_0_7'] as $item)
                                <li>{{ $item->garment_code }} — {{ $item->estimated_days_display }} hari lagi</li>
                            @empty
                                <li class="text-slate-400">Tidak ada.</li>
                            @endforelse
                        </ul>
                    </div>

                    <div class="border border-yellow-400/40 bg-yellow-50 rounded-lg p-4">
                        <p class="text-xs font-mono uppercase tracking-widest text-yellow-600 mb-2">
                            8-14 Hari · Mulai Proses
                        </p>
                        <p class="font-display font-bold text-2xl text-slate-800 mb-2">
                            {{ $procurementDecision['bucket_8_14']->count() }} item
                        </p>
                        <ul class="text-sm text-slate-600 space-y-1 max-h-48 overflow-y-auto">
                            @forelse($procurementDecision['bucket_8_14'] as $item)
                                <li>{{ $item->garment_code }} — {{ $item->estimated_days_display }} hari lagi</li>
                            @empty
                                <li class="text-slate-400">Tidak ada.</li>
                            @endforelse
                        </ul>
                    </div>

                    <div class="border border-emerald-500/30 bg-emerald-50 rounded-lg p-4">
                        <p class="text-xs font-mono uppercase tracking-widest text-emerald-600 mb-2">
                            &gt;14 Hari · Aman
                        </p>
                        <p class="font-display font-bold text-2xl text-slate-800 mb-2">
                            {{ $procurementDecision['bucket_15plus']->count() }} item
                        </p>
                        <p class="text-sm text-slate-500">Cukup dipantau berkala, belum perlu tindakan.</p>
                    </div>

                </div>
            </div>
        </div>

        {{--
            ================= REKOMENDASI GANTI TAG (BARU) =================
            PERUBAHAN: versi FR-16/17 khusus TAG. Tag tidak "berjalan per
            hari" seperti baju (dia dipakai lewat baju yang bisa
            gonta-ganti via Pindah Tag), jadi bucket-nya berdasarkan SISA
            SIKLUS absolut (bukan estimasi hari) — lihat
            AnalyticsController@getTagWearDecision.
        --}}
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm mt-6">
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>

                    <h2 class="font-display font-semibold text-slate-800">Rekomendasi Ganti Tag</h2>
                    <p class="text-sm text-slate-500">
                        Kelompokkan tag berdasarkan sisa siklus terhadap batas maksimalnya sendiri
                        (<code>rated_max_cycles</code>), terpisah dari umur baju.
                    </p>
                </div>
                <button type="button" onclick="toggleTagWearPanel()"
                    class="shrink-0 px-4 py-2 rounded-lg text-sm font-medium bg-[var(--copper)] text-white hover:opacity-90">
                    Generate Rekomendasi Ganti Tag
                </button>
            </div>

            <div id="tagWearPanel" class="hidden mt-5 border-t border-slate-100 pt-5">

                <p class="text-xs text-slate-400 mb-4">
                    Dihasilkan: {{ $tagWearDecision['generated_at'] }}
                </p>

                <div class="space-y-2 mb-5">
                    @foreach ($tagWearDecision['rekomendasi'] as $teks)
                        <div
                            class="flex items-start gap-2 text-sm text-slate-700 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2">
                            <span>💡</span>
                            <span>{{ $teks }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    <div class="border border-[var(--red)]/30 bg-[var(--red)]/5 rounded-lg p-4">
                        <p class="text-xs font-mono uppercase tracking-widest text-[var(--red)] mb-2">
                            &le;10 Siklus · Ganti Sekarang
                        </p>
                        <p class="font-display font-bold text-2xl text-slate-800 mb-2">
                            {{ $tagWearDecision['bucket_urgent']->count() }} tag
                        </p>
                        <ul class="text-sm text-slate-600 space-y-1 max-h-48 overflow-y-auto">
                            @forelse($tagWearDecision['bucket_urgent'] as $item)
                                <li>{{ $item->tag_uid }} — sisa {{ $item->remaining_cycles }} siklus</li>
                            @empty
                                <li class="text-slate-400">Tidak ada.</li>
                            @endforelse
                        </ul>
                    </div>

                    <div class="border border-yellow-400/40 bg-yellow-50 rounded-lg p-4">
                        <p class="text-xs font-mono uppercase tracking-widest text-yellow-600 mb-2">
                            11-30 Siklus · Siapkan Pengganti
                        </p>
                        <p class="font-display font-bold text-2xl text-slate-800 mb-2">
                            {{ $tagWearDecision['bucket_soon']->count() }} tag
                        </p>
                        <ul class="text-sm text-slate-600 space-y-1 max-h-48 overflow-y-auto">
                            @forelse($tagWearDecision['bucket_soon'] as $item)
                                <li>{{ $item->tag_uid }} — sisa {{ $item->remaining_cycles }} siklus</li>
                            @empty
                                <li class="text-slate-400">Tidak ada.</li>
                            @endforelse
                        </ul>
                    </div>

                    <div class="border border-emerald-500/30 bg-emerald-50 rounded-lg p-4">
                        <p class="text-xs font-mono uppercase tracking-widest text-emerald-600 mb-2">
                            &gt;30 Siklus · Aman
                        </p>
                        <p class="font-display font-bold text-2xl text-slate-800 mb-2">
                            {{ $tagWearDecision['bucket_safe']->count() }} tag
                        </p>
                        <p class="text-sm text-slate-500">Cukup dipantau berkala, belum perlu tindakan.</p>
                    </div>

                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // FR-16 & FR-17: buka/tutup panel rekomendasi pengadaan.
        // Data sudah dirender server-side bareng halaman ini, jadi tombol
        // ini murni toggle tampilan — tidak perlu request/AJAX tambahan.
        function toggleProcurementPanel() {
            const panel = document.getElementById('procurementPanel');
            panel.classList.toggle('hidden');
            if (!panel.classList.contains('hidden')) {
                panel.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }

        // Kalau diakses dari sidebar via /admin/analytics#procurementPanel,
        // langsung buka panelnya otomatis.
        if (window.location.hash === '#procurementPanel') {
            document.getElementById('procurementPanel').classList.remove('hidden');
            document.getElementById('procurementPanel').scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }

        // PERUBAHAN: toggle panel Rekomendasi Ganti Tag, pola sama persis
        // dengan toggleProcurementPanel() di atas (baju), cuma target beda.
        function toggleTagWearPanel() {
            const panel = document.getElementById('tagWearPanel');
            panel.classList.toggle('hidden');
            if (!panel.classList.contains('hidden')) {
                panel.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }

        if (window.location.hash === '#tagWearPanel') {
            document.getElementById('tagWearPanel').classList.remove('hidden');
            document.getElementById('tagWearPanel').scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    </script>
    <script>
        const copper = getComputedStyle(document.documentElement).getPropertyValue('--copper').trim() || '#B5651D';
        const red = getComputedStyle(document.documentElement).getPropertyValue('--red').trim() || '#C0392B';
        const yellow = '#FACC15'; // kuning untuk zona "waspada" (dipisah dari --copper supaya jelas beda dgn brand accent)

        // Chart 1: Tren Scan
        new Chart(document.getElementById('scanTrendChart'), {
            type: 'line',
            data: {
                labels: @json($scanTrend['labels']),
                datasets: [{
                    label: 'Jumlah Scan',
                    data: @json($scanTrend['data']),
                    borderColor: copper,
                    backgroundColor: copper + '22',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 3,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });

        // Chart 2: Distribusi Umur Pakai
        new Chart(document.getElementById('ageDistributionChart'), {
            type: 'doughnut',
            data: {
                labels: ['Aman', 'Waspada', 'Kritis'],
                datasets: [{
                    data: [
                        {{ $ageDistribution['aman'] }},
                        {{ $ageDistribution['waspada'] }},
                        {{ $ageDistribution['kritis'] }}
                    ],
                    backgroundColor: ['#10B981', yellow, red],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Chart 4 (BARU): Distribusi Keausan Tag — dataset terpisah dari
        // Chart 2 di atas, karena basisnya tags.rated_max_cycles, bukan
        // garments.max_cycle_limit.
        new Chart(document.getElementById('tagWearChart'), {
            type: 'doughnut',
            data: {
                labels: ['Aman', 'Waspada', 'Kritis'],
                datasets: [{
                    data: [
                        {{ $tagWearDistribution['aman'] }},
                        {{ $tagWearDistribution['waspada'] }},
                        {{ $tagWearDistribution['kritis'] }}
                    ],
                    backgroundColor: ['#10B981', yellow, red],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
@endpush
