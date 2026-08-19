<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditTrailController extends Controller
{
    /**
     * FR-10: Pencatatan siapa yang mengubah data master, kapan, dan nilai
     * lama vs baru. Versi ini pakai Query Builder murni (tanpa Eloquent),
     * menyesuaikan stack project.
     */
    public function index(Request $request)
    {
        $query = DB::table('audit_logs as al')
            ->leftJoin('users as u', 'u.user_id', '=', 'al.changed_by')
            ->select('al.*', 'u.full_name as changed_by_name');

        $query->when($request->filled('table_name'), function ($q) use ($request) {
            $q->where('al.table_name', $request->input('table_name'));
        });

        $query->when($request->filled('action'), function ($q) use ($request) {
            $q->where('al.action', $request->input('action'));
        });

        $query->when($request->filled('changed_by'), function ($q) use ($request) {
            $q->where('al.changed_by', $request->input('changed_by'));
        });

        $query->when($request->filled('q'), function ($q) use ($request) {
            $q->where('al.record_id', 'like', '%' . $request->input('q') . '%');
        });

        $query->when($request->filled('date_from'), function ($q) use ($request) {
            $q->whereDate('al.changed_at', '>=', $request->input('date_from'));
        });

        $query->when($request->filled('date_to'), function ($q) use ($request) {
            $q->whereDate('al.changed_at', '<=', $request->input('date_to'));
        });

        $logs = $query->orderByDesc('al.changed_at')
            ->paginate(25)
            ->withQueryString();

        // Decode JSON, hitung field yang berubah, dan cari label yang lebih
        // manusiawi untuk record_id (mis. garment_code, bukan cuma UUID).
        $logs->getCollection()->transform(function ($log) {
            $old = $log->old_value ? json_decode($log->old_value, true) : null;
            $new = $log->new_value ? json_decode($log->new_value, true) : null;

            $log->changed_fields = $this->computeDiff($log->action, $old, $new);
            $log->record_label   = $this->resolveRecordLabel($log->table_name, $log->record_id);

            return $log;
        });

        return view('admin.audit-trail.index', [
            'logs'       => $logs,
            'tableNames' => DB::table('audit_logs')->distinct()->orderBy('table_name')->pluck('table_name'),
            'users'      => DB::table('users')->orderBy('full_name')->get(['user_id', 'full_name']),
            'filters'    => $request->only(['table_name', 'action', 'changed_by', 'q', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Ambil hanya field yang benar-benar berubah untuk ditampilkan di view.
     * (Untuk action 'update', data yang tersimpan biasanya sudah berupa
     * diff saja kalau ditulis lewat AuditLogger::logUpdate() — fungsi ini
     * tetap aman dipakai kalau suatu saat datanya berupa full snapshot.)
     */
    private function computeDiff(string $action, ?array $old, ?array $new): array
    {
        $old = $old ?? [];
        $new = $new ?? [];

        if ($action === 'insert') {
            return collect($new)->map(fn($v, $k) => ['field' => $k, 'old' => null, 'new' => $v])->values()->all();
        }

        if ($action === 'delete') {
            return collect($old)->map(fn($v, $k) => ['field' => $k, 'old' => $v, 'new' => null])->values()->all();
        }

        $keys = array_unique(array_merge(array_keys($old), array_keys($new)));
        $diff = [];
        foreach ($keys as $key) {
            $oldVal = $old[$key] ?? null;
            $newVal = $new[$key] ?? null;
            if ($oldVal !== $newVal) {
                $diff[] = ['field' => $key, 'old' => $oldVal, 'new' => $newVal];
            }
        }
        return $diff;
    }

    private function resolveRecordLabel(string $table, string $recordId): ?string
    {
        return match ($table) {
            'garments' => DB::table('garments')->where('garment_id', $recordId)->value('garment_code'),
            'tags'     => DB::table('tags')->where('tag_id', $recordId)->value('tag_uid'),
            'devices'  => DB::table('devices')->where('device_id', $recordId)->value('device_name'),
            'users'    => DB::table('users')->where('user_id', $recordId)->value('full_name'),
            default    => null,
        };
    }
}
