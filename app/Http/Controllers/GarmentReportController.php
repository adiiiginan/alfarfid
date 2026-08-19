<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Laporan PDF: Status Baju Waspada & Kritis
 *
 * PERUBAHAN: sekarang ikut join ke tabel `tags` (lewat garments.current_tag_id)
 * supaya laporan punya 2 kolom cycle terpisah — Sterile Garment
 * (garments.max_cycle_limit) dan RFID Tag (tags.rated_max_cycles) — sesuai
 * desain referensi. Baju tanpa tag terpasang tetap muncul di laporan,
 * cuma kolom Tag-nya kosong ('-').
 *
 * Dipakai untuk 2 skenario:
 *  1. Trigger manual dari halaman Analytics (tombol "Download Laporan PDF")
 *     -> method downloadGarmentStatusReport()
 *  2. Trigger otomatis tiap akhir bulan lewat scheduled command
 *     (lihat app/Console/Commands/GenerateMonthlyGarmentStatusReport.php,
 *     yang memanggil buildGarmentStatusReportData() di bawah).
 *
 * Isi laporan CUMA baju dengan status Waspada (estimasi 8-14 hari) dan
 * Kritis (estimasi <=7 hari) -- baju "Aman" (estimasi >14 hari atau belum
 * ada histori scan) tidak ditampilkan per-baris, tapi TETAP dihitung
 * jumlahnya untuk ringkasan (total_normal), supaya donat/ringkasan di
 * laporan menunjukkan gambaran utuh, bukan cuma yang bermasalah.
 */
class GarmentReportController extends Controller
{
    /**
     * Route: GET /admin/analytics/laporan/download
     * Tombol manual di halaman Analytics.
     */
    public function downloadGarmentStatusReport(Request $request)
    {
        $data = $this->buildGarmentStatusReportData();

        $pdf = Pdf::loadView('admin.reports.garment_status_pdf', $data)
            ->setPaper('a4', 'portrait');

        $filename     = 'laporan-status-baju-' . now()->format('Y-m-d_His') . '.pdf';
        $relativePath = 'reports/garment-status/' . $filename;

        Storage::disk('public')->put($relativePath, $pdf->output());

        DB::table('reports_generated')->insert([
            'report_id'        => (string) Str::uuid(),
            'report_type'      => 'garment_status_warning_critical',
            'generated_by'     => Auth::id(), // admin yang klik tombol
            'date_range_start' => $data['periode_awal']->toDateString(),
            'date_range_end'   => $data['periode_akhir']->toDateString(),
            'file_path'        => $relativePath,
            'generated_at'     => now(),
        ]);

        return $pdf->download($filename);
    }

    /**
     * Kumpulkan data untuk laporan: semua baju aktif dengan status
     * Waspada (8-14 hari) atau Kritis (<=7 hari), diurutkan dari yang
     * paling mendesak (estimasi hari paling kecil duluan), plus data
     * tag RFID yang sedang terpasang di masing-masing baju.
     *
     * Method ini public/protected (bukan private) supaya bisa dipanggil
     * juga dari GenerateMonthlyGarmentStatusReport command.
     */
    public function buildGarmentStatusReportData(): array
    {
        $periodeAwal  = now()->startOfMonth();
        $periodeAkhir = now()->endOfMonth();

        $items = DB::table('vw_garment_retirement_forecast as f')
            ->join('garments as g', 'g.garment_id', '=', 'f.garment_id')
            ->leftJoin('garment_categories as gc', 'gc.category_id', '=', 'g.category_id')
            ->leftJoin('divisions as d', 'd.division_id', '=', 'g.division_id')
            // PERUBAHAN: join tag yang sedang terpasang di baju ini.
            // Pakai leftJoin karena garment boleh belum punya tag
            // (current_tag_id NULL) — baju tetap harus muncul di laporan.
            ->leftJoin('tags as t', 't.tag_id', '=', 'g.current_tag_id')
            ->whereNotNull('f.estimated_days_remaining')
            ->where('f.estimated_days_remaining', '<=', 14) // <-- cuma Waspada + Kritis
            ->select([
                'f.garment_code',
                'f.current_cycle_count',
                'f.max_cycle_limit',
                'f.estimated_days_remaining',
                'gc.category_name',
                'd.division_name',
                'g.size',
                't.tag_uid',
                't.total_cycles_used',
                't.rated_max_cycles',
            ])
            ->orderBy('f.estimated_days_remaining') // paling kritis duluan
            ->get()
            ->map(function ($item) {
                $item->pct_terpakai = round($item->current_cycle_count / $item->max_cycle_limit * 100, 1);
                $item->estimated_days_display = (int) ceil($item->estimated_days_remaining); // dibulatkan ke atas
                $item->status = $item->estimated_days_remaining <= 7 ? 'Kritis' : 'Waspada';

                // PERUBAHAN: persentase keausan tag, dihitung terpisah dari
                // persentase garment. Null kalau baju ini belum ada tag
                // terpasang (t.tag_uid akan NULL dari leftJoin).
                $item->tag_pct_terpakai = ($item->tag_uid && $item->rated_max_cycles)
                    ? round($item->total_cycles_used / $item->rated_max_cycles * 100, 1)
                    : null;

                return $item;
            });

        $totalKritis  = $items->where('status', 'Kritis')->count();
        $totalWaspada = $items->where('status', 'Waspada')->count();

        // PERUBAHAN: hitung total baju Normal (aman) supaya ringkasan/donat
        // di laporan bisa menunjukkan proporsi utuh (Kritis + Waspada +
        // Normal = total baju aktif), bukan cuma 2 kategori yang bermasalah.
        $totalGarmentAktif = DB::table('garments')->where('status', 'active')->count();
        $totalNormal = max(0, $totalGarmentAktif - $totalKritis - $totalWaspada);

        return [
            'items'                => $items,
            'total_kritis'         => $totalKritis,
            'total_waspada'        => $totalWaspada,
            'total_normal'         => $totalNormal,
            'total_garment_aktif'  => $totalGarmentAktif,
            'periode_awal'         => $periodeAwal,
            'periode_akhir'        => $periodeAkhir,
            'generated_at'         => now(),
        ];
    }
}
