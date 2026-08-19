<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Garment;
use App\Models\ScanEvent;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'kpi'          => $this->buildKpi(),
            'recentScans'  => ScanEvent::with(['garment', 'tag', 'device'])
                ->orderByDesc('scan_timestamp')
                ->limit(8)
                ->get(),
        ]);
    }

    private function buildKpi(): array
    {
        $totalGarmentAktif = Garment::where('status', 'active')->count();
        $totalTagAktif     = Tag::where('status', 'active')->count();

        $scanHariIni = ScanEvent::whereDate('scan_timestamp', now()->toDateString())->count();

        // Device dianggap online kalau status-nya 'online' DAN sempat heartbeat
        // dalam 5 menit terakhir — jaga-jaga kalau status di DB nyangkut
        // 'online' padahal device sebenarnya sudah mati/putus koneksi lama.
        $deviceTotal  = Device::count();
        $deviceOnline = Device::where('status', 'online')
            ->where('last_heartbeat_at', '>=', now()->subMinutes(5))
            ->count();

        // Item Kritis: garment yang cycle GARMENT-nya >=90% ATAU cycle TAG
        // yang sedang terpasang >=90% (dua basis berbeda, lihat konteks
        // aplikasi ini — max_cycle_limit vs rated_max_cycles). Satu garment
        // dihitung sekali meski kedua sisi kritis sekaligus.
        $itemKritis = DB::table('garments as g')
            ->leftJoin('tags as t', 't.tag_id', '=', 'g.current_tag_id')
            ->where('g.status', 'active')
            ->where(function ($q) {
                $q->whereRaw('g.current_cycle_count >= g.max_cycle_limit * 0.9')
                    ->orWhereRaw('t.total_cycles_used >= t.rated_max_cycles * 0.9');
            })
            ->count();

        return [
            'garment_aktif'  => $totalGarmentAktif,
            'tag_aktif'      => $totalTagAktif,
            'item_kritis'    => $itemKritis,
            'scan_hari_ini'  => $scanHariIni,
            'device_online'  => $deviceOnline,
            'device_total'   => $deviceTotal,
        ];
    }
}
