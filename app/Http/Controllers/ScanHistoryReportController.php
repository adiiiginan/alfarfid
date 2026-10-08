<?php

namespace App\Http\Controllers;

use App\Models\ReportGenerated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ScanHistoryReportController extends Controller
{
    private const DISK = 'public';

    /**
     * Label tampilan untuk tiap report_type yang ada di database.
     * Tambahkan baris baru di sini kalau nanti ada jenis laporan lain.
     */
    private const TYPE_LABELS = [
        'garment_status_warning_critical' => 'Status Baju (Waspada & Kritis)',
        'scan_history'                    => 'Riwayat Scan',
    ];

    /**
     * GET /admin/reports
     * Halaman arsip: daftar semua laporan yang pernah digenerate.
     * TIDAK ada fitur generate baru di halaman ini.
     */
    public function index()
    {
        return view('admin.reports.scan-history', [
            'reportTypes' => self::TYPE_LABELS,
        ]);
    }
    /**
     * GET /admin/reports/data
     * JSON list untuk tabel arsip, dengan filter jenis & tanggal + pagination.
     */
    public function data(Request $request)
    {
        $perPage = (int) $request->input('per_page', 15);
        $perPage = max(5, min($perPage, 50));

        $query = ReportGenerated::with('generatedByUser')
            ->orderByDesc('generated_at');

        if ($request->filled('report_type')) {
            $query->where('report_type', $request->report_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('generated_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('generated_at', '<=', $request->date_to);
        }

        $logs = $query->paginate($perPage, ['*'], 'page', (int) $request->input('page', 1));

        return response()->json([
            'logs' => $logs->getCollection()->map(function (ReportGenerated $log) {
                return [
                    'report_id'    => $log->report_id,
                    'report_type'  => $log->report_type,
                    'type_label'   => self::TYPE_LABELS[$log->report_type] ?? $log->report_type,
                    'user_name'    => $log->generatedByUser->full_name ?? 'Sistem',
                    'date_range'   => $log->date_range_start && $log->date_range_end
                        ? $log->date_range_start->format('d/m/Y') . ' – ' . $log->date_range_end->format('d/m/Y')
                        : '-',
                    'file_exists'  => $log->file_path ? Storage::disk(self::DISK)->exists($log->file_path) : false,
                    'generated_at' => $log->generated_at->translatedFormat('d M Y, H:i'),
                ];
            })->values(),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'last_page'    => $logs->lastPage(),
                'total'        => $logs->total(),
            ],
        ]);
    }

    /**
     * GET /admin/reports/reissue/{reportGenerated}
     * Unduh ulang file yang sudah pernah digenerate — apapun jenisnya.
     * Murni serve file fisik yang sama persis, tanpa regenerate.
     */
    public function reissue(ReportGenerated $reportGenerated)
    {
        if (! $reportGenerated->file_path || ! Storage::disk(self::DISK)->exists($reportGenerated->file_path)) {
            return back()->with('error', 'File laporan ini sudah tidak ada di server (mungkin terhapus).');
        }

        $filename = basename($reportGenerated->file_path);

        return response()->download(
            $this->absolutePath($reportGenerated->file_path),
            $filename
        );
    }

    private function absolutePath(string $relativePath): string
    {
        return storage_path('app/public/' . ltrim($relativePath, '/'));
    }
}
