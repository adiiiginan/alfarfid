<?php

namespace App\Http\Controllers;

// Route yang perlu ditambahkan di routes/web.php, di dalam grup admin
// (sejajar dengan blok "SCAN EVENTS"):
//
//   // ================= ANALYTICS & GRAFIK (FR-07) =================
//   Route::get('analytics', [\App\Http\Controllers\AnalyticsController::class, 'index'])
//       ->name('analytics.index');
//
// Nama route lengkapnya jadi: admin.analytics.index
// URL akses: /admin/analytics

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * FR-07 — Analytics & Grafik
 *
 * Tiga visualisasi ASLI (baju):
 *  1. Tren jumlah scan harian/mingguan   (tabel: scan_events)
 *  2. Distribusi umur pakai item          (tabel: garments)
 *  3. Prediksi waktu retired              (view : vw_garment_retirement_forecast)
 *
 * ── PERUBAHAN (patch batas siklus tag) ──────────────────────────────
 * Baju (max_cycle_limit, default 50) dan tag (rated_max_cycles, default
 * 200) punya umur pakai yang beda dan TIDAK linear terhadap satu sama
 * lain — satu tag bisa dipindah lintas beberapa baju lewat "Pindah
 * Tag" (lihat TagReassignmentController), jadi tag bisa mendekati
 * batas umurnya sendiri walau baju yang sedang dipakainya masih baru.
 * Sebelumnya modul Analytics ini HANYA memantau umur baju — tidak ada
 * visibilitas sama sekali soal umur tag. Ditambahkan chart ke-4 +
 * bucket keputusan terpisah khusus tag di bawah.
 *
 * Chart baru:
 *  4. Distribusi keausan tag berdasarkan tags.total_cycles_used
 *     terhadap tags.rated_max_cycles (terpisah dari chart 2 yang
 *     berbasis baju).
 *
 * Catatan skema (dari alfa.sql):
 *  - scan_events.scan_timestamp -> kolom waktu scan (bukan scanned_at)
 *  - scan_events.event_type     -> 'pre_autoclave' | 'post_autoclave' | 'usage_checkpoint' | 'manual_reentry'
 *    Untuk tren "berapa baju yang lewat proses autoclave", kita hitung event_type
 *    pre_autoclave + post_autoclave saja (usage_checkpoint bukan proses autoclave).
 *  - vw_garment_retirement_forecast sudah menghitung estimated_days_remaining
 *    berdasarkan rata-rata cycle 14 hari terakhir, jadi tidak perlu dihitung ulang di PHP.
 */
class AnalyticsController extends Controller
{
    /**
     * Ambang batas zona "kritis" dipakai konsisten untuk baju MAUPUN
     * tag: >= 90% dari batas siklus masing-masing.
     */
    private const CRITICAL_THRESHOLD = 0.9;
    private const WARNING_THRESHOLD  = 0.7;

    public function index(Request $request)
    {
        $period = $request->query('period', 'daily'); // daily | weekly
        $range  = (int) $request->query('range', 30);  // jumlah hari ke belakang

        $scanTrend          = $this->getScanTrend($period, $range);
        $ageDistribution    = $this->getAgeDistribution();
        $retirementForecast = $this->getRetirementForecast();

        // ── BARU: distribusi & forecast keausan TAG, terpisah dari baju.
        $tagWearDistribution = $this->getTagWearDistribution();
        $tagWearForecast     = $this->getTagWearForecast();

        // Ringkasan angka untuk kartu di atas grafik
        $summary = [
            'total_scan_range'    => array_sum($scanTrend['data']),
            'total_garment_aktif' => array_sum($ageDistribution),
            'akan_retired_7hari'  => $retirementForecast->where('estimated_days_remaining', '<=', 7)->count(),
            'akan_retired_14hari' => $retirementForecast->where('estimated_days_remaining', '<=', 14)->count(),
            // ── BARU: ringkasan tag, supaya kelihatan di kartu atas juga.
            'total_tag_aktif'     => array_sum($tagWearDistribution),
            'tag_kritis'          => $tagWearDistribution['kritis'] ?? 0,
        ];

        // FR-16 & FR-17 digabung di halaman ini (bukan menu terpisah):
        // bucket 0-7 / 8-14 / >14 hari + rekomendasi pengadaan siap pakai
        // untuk tombol "Generate Rekomendasi Pengadaan".
        $procurementDecision = $this->getProcurementDecision();

        // ── BARU: rekomendasi khusus tag yang sudah mepet/lewat batas
        // siklusnya sendiri — supaya tim tahu tag mana yang HARUS
        // di-retired lewat "Ganti Tag", walau bajunya sendiri masih sehat.
        $tagWearDecision = $this->getTagWearDecision();

        return view('admin.analytics', compact(
            'scanTrend',
            'ageDistribution',
            'retirementForecast',
            'summary',
            'period',
            'range',
            'procurementDecision',
            'tagWearDistribution',
            'tagWearForecast',
            'tagWearDecision'
        ));
    }

    /**
     * Chart 1: Tren jumlah scan (proses autoclave) harian atau mingguan.
     */
    private function getScanTrend(string $period, int $range): array
    {
        $query = DB::table('scan_events')
            ->whereIn('event_type', ['pre_autoclave', 'post_autoclave'])
            ->where('scan_timestamp', '>=', Carbon::now()->subDays($range));

        if ($period === 'weekly') {
            $rows = $query
                ->selectRaw('YEARWEEK(scan_timestamp, 3) as period_key')
                ->selectRaw('MIN(DATE(scan_timestamp)) as period_start')
                ->selectRaw('COUNT(*) as total')
                ->groupBy('period_key')
                ->orderBy('period_key')
                ->get();

            $labels = $rows->map(fn($r) => 'Mgg ' . Carbon::parse($r->period_start)->format('d M'))->toArray();
        } else {
            $rows = $query
                ->selectRaw('DATE(scan_timestamp) as period_key')
                ->selectRaw('COUNT(*) as total')
                ->groupBy('period_key')
                ->orderBy('period_key')
                ->get();

            $labels = $rows->map(fn($r) => Carbon::parse($r->period_key)->format('d M'))->toArray();
        }

        return [
            'labels' => $labels,
            'data'   => $rows->pluck('total')->map(fn($v) => (int) $v)->toArray(),
        ];
    }

    /**
     * Chart 2: Distribusi umur pakai item (zona aman / waspada / kritis)
     * berdasarkan current_cycle_count terhadap max_cycle_limit (BAJU).
     *
     * Ambang batas zona bisa disesuaikan; default:
     *  - aman    : < 70% dari batas siklus
     *  - waspada : 70% - 89%
     *  - kritis  : >= 90%
     */
    private function getAgeDistribution(): array
    {
        $rows = DB::table('garments')
            ->where('status', 'active')
            ->selectRaw("
                CASE
                    WHEN (current_cycle_count / max_cycle_limit) >= 0.9 THEN 'kritis'
                    WHEN (current_cycle_count / max_cycle_limit) >= 0.7 THEN 'waspada'
                    ELSE 'aman'
                END as zone
            ")
            ->selectRaw('COUNT(*) as total')
            ->groupBy('zone')
            ->pluck('total', 'zone');

        return [
            'aman'    => (int) ($rows['aman'] ?? 0),
            'waspada' => (int) ($rows['waspada'] ?? 0),
            'kritis'  => (int) ($rows['kritis'] ?? 0),
        ];
    }

    /**
     * Chart 3: Prediksi waktu retired, diambil langsung dari view
     * vw_garment_retirement_forecast yang sudah ada di database.
     * Difilter ke item yang punya estimasi (bukan NULL) dan dalam horizon tertentu,
     * supaya nyambung ke FR-17 (forecast stok pengganti 7/14 hari).
     */
    private function getRetirementForecast(int $horizonDays = 30)
    {
        return DB::table('vw_garment_retirement_forecast')
            ->whereNotNull('estimated_days_remaining')
            ->where('estimated_days_remaining', '<=', $horizonDays)
            ->orderBy('estimated_days_remaining')
            ->limit(20)
            ->get()
            ->map(function ($item) {
                $item->estimated_days_display = (int) ceil($item->estimated_days_remaining); // <-- tambahan
                return $item;
            });
    }

    /**
     * Chart 4 (BARU): Distribusi keausan TAG (zona aman / waspada / kritis)
     * berdasarkan total_cycles_used terhadap rated_max_cycles.
     *
     * SENGAJA dipisah dari getAgeDistribution() (yang berbasis baju),
     * karena keduanya punya siklus hidup independen — tag bisa
     * "kritis" walau baju yang sedang menempel padanya masih "aman",
     * dan sebaliknya.
     */
    private function getTagWearDistribution(): array
    {
        $rows = DB::table('tags')
            ->where('status', 'active')
            ->selectRaw('
                CASE
                    WHEN (total_cycles_used / rated_max_cycles) >= ' . self::CRITICAL_THRESHOLD . ' THEN \'kritis\'
                    WHEN (total_cycles_used / rated_max_cycles) >= ' . self::WARNING_THRESHOLD . ' THEN \'waspada\'
                    ELSE \'aman\'
                END as zone
            ')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('zone')
            ->pluck('total', 'zone');

        return [
            'aman'    => (int) ($rows['aman'] ?? 0),
            'waspada' => (int) ($rows['waspada'] ?? 0),
            'kritis'  => (int) ($rows['kritis'] ?? 0),
        ];
    }

    /**
     * Daftar tag aktif diurutkan dari yang paling mepet ke batas
     * rated_max_cycles-nya sendiri. Beda dengan
     * vw_garment_retirement_forecast (yang butuh estimasi HARI
     * berdasar rata-rata pemakaian harian), forecast tag di sini pakai
     * SISA SIKLUS langsung karena tag tidak "dipakai per hari" secara
     * langsung — dia dipakai lewat baju yang menempel padanya, dan
     * baju itu bisa berganti-ganti (Pindah Tag). Sisa siklus absolut
     * jauh lebih bermakna di sini daripada estimasi hari.
     */
    private function getTagWearForecast(int $limit = 20)
    {
        return DB::table('tags')
            ->where('status', 'active')
            ->selectRaw('
                tag_id,
                tag_uid,
                rated_max_cycles,
                total_cycles_used,
                (rated_max_cycles - total_cycles_used) as remaining_cycles,
                ROUND(total_cycles_used / rated_max_cycles * 100, 1) as usage_percent
            ')
            ->orderByDesc('usage_percent')
            ->limit($limit)
            ->get();
    }

    /**
     * FR-16 (Proyeksi Sisa Umur Pakai) + FR-17 (Forecast Kebutuhan Stok Pengganti),
     * digabung jadi satu ringkasan keputusan langsung di halaman Analytics
     * (bukan menu terpisah), supaya QA/Manajemen/Procurement lihat insight
     * DAN keputusan actionable-nya di satu tempat yang sama.
     *
     * Mengelompokkan seluruh item aktif (bukan cuma yang di-limit 20 di chart 3)
     * ke 3 rentang sesuai PRD section 9.5:
     *   - 0-7 hari   -> beli/rotasi SEKARANG
     *   - 8-14 hari  -> mulai proses pengadaan minggu ini
     *   - >14 hari   -> aman, cukup dipantau
     */
    private function getProcurementDecision(): array
    {
        $all = DB::table('vw_garment_retirement_forecast')
            ->whereNotNull('estimated_days_remaining')
            ->orderBy('estimated_days_remaining')
            ->get()
            ->map(function ($item) {
                $item->estimated_days_display = (int) ceil($item->estimated_days_remaining); // <-- tambahan
                return $item;
            });

        $bucket0_7    = $all->where('estimated_days_remaining', '<=', 7)->values();
        $bucket8_14   = $all->where('estimated_days_remaining', '>', 7)
            ->where('estimated_days_remaining', '<=', 14)->values();
        $bucket15plus = $all->where('estimated_days_remaining', '>', 14)->values();

        $rekomendasi = [];

        if ($bucket0_7->isNotEmpty()) {
            $contoh = $bucket0_7->pluck('garment_code')->take(5)->implode(', ');
            $rekomendasi[] = sprintf(
                '%d item perlu dibelikan/dirotasi pengganti MINGGU INI juga (estimasi retired ≤ 7 hari): %s%s.',
                $bucket0_7->count(),
                $contoh,
                $bucket0_7->count() > 5 ? ', dst.' : ''
            );
        }

        if ($bucket8_14->isNotEmpty()) {
            $rekomendasi[] = sprintf(
                '%d item sebaiknya mulai masuk proses pengadaan dalam 1-2 minggu ke depan (estimasi retired 8-14 hari).',
                $bucket8_14->count()
            );
        }

        if ($bucket0_7->isEmpty() && $bucket8_14->isEmpty()) {
            $rekomendasi[] = 'Tidak ada item mendesak — semua item yang punya estimasi masih di atas 14 hari, belum perlu tindakan pengadaan darurat.';
        }

        return [
            'bucket_0_7'    => $bucket0_7,
            'bucket_8_14'   => $bucket8_14,
            'bucket_15plus' => $bucket15plus,
            'rekomendasi'   => $rekomendasi,
            'generated_at'  => now()->format('d M Y, H:i'),
        ];
    }

    /**
     * BARU — versi FR-16/FR-17 khusus TAG. Karena tag tidak retired
     * berdasarkan tanggal/hari (dia "aus" berdasarkan berapa kali
     * dipakai, lintas baju), bucket-nya dibuat berdasarkan SISA SIKLUS
     * absolut, bukan estimasi hari:
     *   - sisa <= 10 siklus   -> retired/ganti SEKARANG (pakai "Ganti Tag")
     *   - sisa 11-30 siklus   -> mulai siapkan tag pengganti
     *   - sisa > 30 siklus    -> aman
     *
     * Angka ambang (10 / 30) sengaja dibuat konstanta di bawah supaya
     * gampang disesuaikan tanpa harus ubah logic.
     */
    private function getTagWearDecision(): array
    {
        $urgentThreshold = 10;
        $soonThreshold    = 30;

        $all = DB::table('tags')
            ->where('status', 'active')
            ->selectRaw('
                tag_id,
                tag_uid,
                rated_max_cycles,
                total_cycles_used,
                (rated_max_cycles - total_cycles_used) as remaining_cycles
            ')
            ->orderBy('remaining_cycles')
            ->get();

        $bucketUrgent = $all->where('remaining_cycles', '<=', $urgentThreshold)->values();
        $bucketSoon   = $all->where('remaining_cycles', '>', $urgentThreshold)
            ->where('remaining_cycles', '<=', $soonThreshold)->values();
        $bucketSafe   = $all->where('remaining_cycles', '>', $soonThreshold)->values();

        $rekomendasi = [];

        if ($bucketUrgent->isNotEmpty()) {
            $contoh = $bucketUrgent->pluck('tag_uid')->take(5)->implode(', ');
            $rekomendasi[] = sprintf(
                '%d tag tersisa ≤ %d siklus lagi — segera proses "Ganti Tag" sebelum dipakai lebih jauh: %s%s.',
                $bucketUrgent->count(),
                $urgentThreshold,
                $contoh,
                $bucketUrgent->count() > 5 ? ', dst.' : ''
            );
        }

        if ($bucketSoon->isNotEmpty()) {
            $rekomendasi[] = sprintf(
                '%d tag tersisa %d-%d siklus lagi — siapkan tag pengganti dalam waktu dekat.',
                $bucketSoon->count(),
                $urgentThreshold + 1,
                $soonThreshold
            );
        }

        if ($bucketUrgent->isEmpty() && $bucketSoon->isEmpty()) {
            $rekomendasi[] = 'Tidak ada tag yang mendesak — semua tag aktif masih punya sisa siklus di atas ambang aman.';
        }

        return [
            'bucket_urgent' => $bucketUrgent,
            'bucket_soon'   => $bucketSoon,
            'bucket_safe'   => $bucketSafe,
            'rekomendasi'   => $rekomendasi,
            'generated_at'  => now()->format('d M Y, H:i'),
        ];
    }
}
