<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    /**
     * FR-03: Manajemen Alerts & Notifikasi.
     * Menampilkan daftar alert dengan statistik, filter, dan aksi resolusi.
     */
    public function index(Request $request)
    {
        // Statistik ringkasan
        $stats = [
            'total_open'     => Alert::open()->count(),
            'critical_open'  => Alert::open()->critical()->count(),
            'acknowledged'   => Alert::acknowledged()->count(),
            'resolved_today' => Alert::resolved()->whereDate('resolved_at', now()->toDateString())->count(),
        ];

        $query = Alert::with(['garment', 'tag', 'device', 'resolvedBy']);

        // Filter status
        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        } else {
            // Default tampilkan open + acknowledged jika filter status belum diset
            $query->whereIn('status', ['open', 'acknowledged']);
        }

        // Filter severity
        if ($request->filled('severity') && $request->input('severity') !== 'all') {
            $query->where('severity', $request->input('severity'));
        }

        // Filter alert_type
        if ($request->filled('alert_type') && $request->input('alert_type') !== 'all') {
            $query->where('alert_type', $request->input('alert_type'));
        }

        // Pencarian (q)
        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($q) use ($search) {
                $q->where('message', 'like', "%{$search}%")
                    ->orWhereHas('garment', function ($g) use ($search) {
                        $g->where('garment_code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('tag', function ($t) use ($search) {
                        $t->where('tag_uid', 'like', "%{$search}%");
                    })
                    ->orWhereHas('device', function ($d) use ($search) {
                        $d->where('device_name', 'like', "%{$search}%")
                            ->orWhere('location', 'like', "%{$search}%");
                    });
            });
        }

        // Filter tanggal
        if ($request->filled('date_from')) {
            $query->whereDate('triggered_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('triggered_at', '<=', $request->input('date_to'));
        }

        // Urutan: Open/Critical didahulukan, lalu waktu terbaru
        $alerts = $query->orderByRaw("
                CASE status
                    WHEN 'open' THEN 1
                    WHEN 'acknowledged' THEN 2
                    WHEN 'resolved' THEN 3
                    ELSE 4
                END ASC
            ")
            ->orderByDesc('triggered_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.alerts.index', [
            'alerts'  => $alerts,
            'stats'   => $stats,
            'filters' => $request->all(),
        ]);
    }

    /**
     * Tandai alert sudah dibaca / diketahui (acknowledged).
     */
    public function acknowledge(Alert $alert)
    {
        if ($alert->status === 'open') {
            $alert->update([
                'status' => 'acknowledged',
            ]);
        }

        return back()->with('success', 'Alert telah ditandai sebagai Acknowledged (diketahui).');
    }

    /**
     * Selesaikan alert (resolved) dan catat user yang menyelesaikan.
     */
    public function resolve(Request $request, Alert $alert)
    {
        $alert->update([
            'status'      => 'resolved',
            'resolved_at' => now(),
            'resolved_by' => auth()->id(),
        ]);

        return back()->with('success', 'Alert telah berhasil diselesaikan (Resolved).');
    }

    /**
     * Aksi massal: tandai beberapa alert sebagai acknowledged.
     */
    public function bulkAcknowledge(Request $request)
    {
        $validated = $request->validate([
            'alert_ids'   => 'required|array|min:1',
            'alert_ids.*' => 'required|string',
        ]);

        Alert::whereIn('alert_id', $validated['alert_ids'])
            ->where('status', 'open')
            ->update(['status' => 'acknowledged']);

        return back()->with('success', count($validated['alert_ids']) . ' alert berhasil ditandai sebagai Acknowledged.');
    }

    /**
     * Aksi massal: selesaikan beberapa alert sekaligus.
     */
    public function bulkResolve(Request $request)
    {
        $validated = $request->validate([
            'alert_ids'   => 'required|array|min:1',
            'alert_ids.*' => 'required|string',
        ]);

        Alert::whereIn('alert_id', $validated['alert_ids'])
            ->where('status', '!=', 'resolved')
            ->update([
                'status'      => 'resolved',
                'resolved_at' => now(),
                'resolved_by' => auth()->id(),
            ]);

        return back()->with('success', count($validated['alert_ids']) . ' alert berhasil diselesaikan (Resolved).');
    }
}
