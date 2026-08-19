<?php

namespace App\Console\Commands;

use App\Http\Controllers\GarmentReportController;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Generate laporan PDF status baju (Waspada & Kritis) secara otomatis.
 * Dijadwalkan jalan di HARI TERAKHIR tiap bulan (lihat app/Console/Kernel.php).
 *
 * Jalankan manual untuk testing:
 *   php artisan reports:garment-status-monthly
 */
class GenerateMonthlyGarmentStatusReport extends Command
{
    protected $signature = 'reports:garment-status-monthly';

    protected $description = 'Generate laporan PDF bulanan untuk baju berstatus Waspada & Kritis';

    public function handle(GarmentReportController $controller): int
    {
        $data = $controller->buildGarmentStatusReportData();

        $pdf = Pdf::loadView('admin.reports.garment_status_pdf', $data)
            ->setPaper('a4', 'portrait');

        $filename     = 'laporan-status-baju-' . now()->format('Y-m') . '.pdf';
        $relativePath = 'reports/garment-status/' . $filename;

        Storage::disk('public')->put($relativePath, $pdf->output());

        DB::table('reports_generated')->insert([
            'report_id'        => (string) Str::uuid(),
            'report_type'      => 'garment_status_warning_critical',
            'generated_by'     => null, // otomatis oleh sistem, bukan user
            'date_range_start' => $data['periode_awal']->toDateString(),
            'date_range_end'   => $data['periode_akhir']->toDateString(),
            'file_path'        => $relativePath,
            'generated_at'     => now(),
        ]);

        $this->info("Laporan bulanan berhasil dibuat: {$relativePath}");
        $this->info("Total Kritis: {$data['total_kritis']} | Total Waspada: {$data['total_waspada']}");

        return self::SUCCESS;
    }
}
