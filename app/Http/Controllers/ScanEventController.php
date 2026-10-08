<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Division;
use App\Models\ScanEvent;
use Illuminate\Http\Request;

class ScanEventController extends Controller
{
    /**
     * Peta kategori tampilan (UI) -> event_type di database.
     * Ubah di sini saja kalau mapping-nya perlu direvisi.
     */
    private const CATEGORY_MAP = [
        'usage'     => ['usage_checkpoint'],
        'laundry'   => ['pre_autoclave'],
        'autoclave' => ['post_autoclave'],
        'other'     => ['manual_reentry'],
    ];

    public function index()
    {
        $view = request()->routeIs('operator.*') ? 'operator.dashboard' : 'admin.scan-events';

        return view($view, [
            'divisions' => Division::orderBy('division_name')->get(),
            'devices'   => Device::orderBy('device_name')->get(),
        ]);
    }

    /**
     * Endpoint JSON untuk polling real-time dari frontend.
     * GET /admin/scan-events/data
     */
    public function data(Request $request)
    {
        $perPage = (int) $request->input('per_page', 20);
        $perPage = max(5, min($perPage, 100)); // batasi wajar, 5–100

        $query = ScanEvent::with([
            'tag.division',
            'garment.category',
            'garment.division',
            'device',
        ])
            ->orderByDesc('scan_timestamp');

        if ($request->filled('category') && $request->category !== 'all') {
            $types = self::CATEGORY_MAP[$request->category] ?? [];
            $query->whereIn('event_type', $types);
        }

        if ($request->filled('division_id')) {
            $divisionId = $request->division_id;
            $query->where(function ($q) use ($divisionId) {
                $q->whereHas('garment', fn($g) => $g->where('division_id', $divisionId))
                    ->orWhereHas('tag', fn($t) => $t->where('division_id', $divisionId));
            });
        }

        if ($request->filled('device_id')) {
            $query->where('device_id', $request->device_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('scan_timestamp', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('scan_timestamp', '<=', $request->date_to);
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', (int) $request->input('page', 1));

        return response()->json([
            'events' => $paginator->getCollection()->map(fn($e) => $this->formatEvent($e))->values(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
            'stats'       => $this->statsToday(),
            'server_time' => now()->toIso8601String(),
        ]);
    }

    private function formatEvent(ScanEvent $e): array
    {
        $garment = $e->garment;
        $tag     = $e->tag;

        $division = $garment->division ?? $tag->division ?? null;
        $timestamp = $e->scan_timestamp;

        return [
            'event_id'          => $e->event_id,
            'event_type'        => $e->event_type,
            'category'          => $this->categoryOf($e->event_type),
            'scan_timestamp'    => optional($timestamp)->toIso8601String(),
            'scan_date_h'       => optional($timestamp)?->locale('id')->translatedFormat('l, d F Y'),
            'scan_time_h'       => optional($timestamp)->format('H:i:s'),
            'scan_timestamp_h'  => optional($timestamp)->format('d/m/Y H:i:s'),
            'tag_uid'           => $tag->tag_uid ?? '-',
            'garment_code'      => $garment->garment_code ?? '-',
            'category_name'     => $garment->category->category_name ?? '-',
            'division_name'     => $division->division_name ?? null,
            'color_hex'         => $division->color_hex ?? '#999999',
            'device_name'       => $e->device->device_name ?? '-',
            'location'          => $e->location,
            'cycle_count_after' => $e->cycle_count_after,

            // Siklus Baju
            'garment_current_cycle_count' => $garment->current_cycle_count ?? $e->cycle_count_after,
            'garment_max_cycle_limit'     => $garment->max_cycle_limit ?? null,

            // Siklus Tag
            'tag_total_cycles_used' => $tag->total_cycles_used ?? null,
            'tag_rated_max_cycles'  => $tag->rated_max_cycles ?? null,
        ];
    }

    private function categoryOf(string $eventType): string
    {
        foreach (self::CATEGORY_MAP as $category => $types) {
            if (in_array($eventType, $types, true)) {
                return $category;
            }
        }
        return 'other';
    }

    private function statsToday(): array
    {
        $rows = ScanEvent::whereDate('scan_timestamp', now()->toDateString())
            ->selectRaw('event_type, COUNT(*) as total')
            ->groupBy('event_type')
            ->pluck('total', 'event_type');

        $result = ['usage' => 0, 'laundry' => 0, 'autoclave' => 0, 'other' => 0];

        foreach (self::CATEGORY_MAP as $category => $types) {
            foreach ($types as $type) {
                $result[$category] += (int) ($rows[$type] ?? 0);
            }
        }

        $result['total'] = array_sum($result);

        return $result;
    }
}
