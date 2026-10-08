<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreScanEventRequest;
use App\Models\Alert;
use App\Models\Device;
use App\Models\ScanEvent;
use App\Models\Tag;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScanEventController extends Controller
{
    private const WARNING_THRESHOLD_RATIO = 0.9;

    /**
     * Alur otomatis: server yang menentukan event_type berikutnya,
     * murni berdasarkan current_stage garment saat ini. Client (ESP32)
     * TIDAK perlu kirim event_type sama sekali — cukup tag_uid.
     *
     * siap_digunakan  -> (scan) -> event: usage_checkpoint -> stage baru: sedang_dipakai
     * sedang_dipakai   -> (scan) -> event: pre_autoclave    -> stage baru: pre_autoclave
     * pre_autoclave    -> (scan) -> event: post_autoclave   -> stage baru: siap_digunakan
     */
    private const STAGE_FLOW = [
        'siap_digunakan' => ['event' => 'usage_checkpoint', 'next_stage' => 'sedang_dipakai'],
        'sedang_dipakai'  => ['event' => 'pre_autoclave',    'next_stage' => 'pre_autoclave'],
        'pre_autoclave'   => ['event' => 'post_autoclave',   'next_stage' => 'siap_digunakan'],
    ];

    /**
     * ── ANTI STRAY-READ ──────────────────────────────────────────────
     * Minimal berapa lama (dalam MENIT) garment harus berada di stage
     * saat ini sebelum boleh pindah ke stage berikutnya. Kalau scan
     * masuk lebih cepat dari ini, dianggap bacaan RFID tidak sengaja
     * (misal tag ikut kebaca padahal cuma lewat dekat reader) dan
     * DITOLAK, bukan diproses sebagai transisi valid.
     *
     * Silakan ubah angka di sini sesuai kondisi lapangan sebenarnya:
     * - siap_digunakan -> sedang_dipakai   : tidak ada batas (baju boleh diambil kapan saja)
     * - sedang_dipakai  -> pre_autoclave    : minimal 9 jam (8 jam dipakai + 1 jam laundry)
     * - pre_autoclave   -> post_autoclave   : minimal 30 menit (proses autoclave)
     */
    private const MIN_DWELL_MINUTES = [
        'siap_digunakan' => 0,
        'sedang_dipakai'  => 9 * 60,  // 540 menit = 9 jam
        'pre_autoclave'   => 30,
    ];

    // Hanya post_autoclave yang menyelesaikan 1 siklus penuh & nambah cycle_count
    private const CYCLE_COMPLETING_EVENT = 'post_autoclave';

    private const REGISTRATION_MODE_KEY = 'registration_mode_active';
    private const PENDING_TAG_KEY = 'registration_pending_tag';

    public function store(StoreScanEventRequest $request)
    {
        $device = $request->attributes->get('device');

        if (! $device instanceof Device) {
            return response()->json([
                'message' => 'Device tidak terautentikasi. Pastikan route ini melewati middleware esp.device.',
            ], 401);
        }

        $data = $request->validated();

        if (Cache::has(self::REGISTRATION_MODE_KEY)) {
            return $this->handleRegistrationScan($device, $data['tag_uid']);
        }

        return $this->handleOperationalScan($device, $data);
    }

    private function handleRegistrationScan(Device $device, string $tagUid)
    {
        Cache::put(self::PENDING_TAG_KEY, [
            'tag_uid'     => $tagUid,
            'device_id'   => $device->device_id,
            'device_name' => $device->device_name,
        ], now()->addSeconds(60));

        $device->forceFill([
            'status' => 'online',
            'last_heartbeat_at' => now(),
        ])->save();

        return response()->json([
            'message' => 'Scan diterima untuk registrasi.',
            'mode'    => 'registration',
            'tag_uid' => $tagUid,
        ]);
    }

    private function handleOperationalScan(Device $device, array $data)
    {
        $tag = Tag::where('tag_uid', $data['tag_uid'])->first();

        if (! $tag) {
            Cache::put(self::PENDING_TAG_KEY, [
                'tag_uid'     => $data['tag_uid'],
                'device_id'   => $device->device_id,
                'device_name' => $device->device_name,
            ], now()->addMinutes(5));

            return response()->json([
                'message' => 'Tag baru diterima untuk registrasi.',
                'mode'    => 'registration',
                'tag_uid' => $data['tag_uid'],
            ], 200);
        }

        if ($tag->status !== 'active') {
            return response()->json([
                'message' => "Tag berstatus '{$tag->status}', tidak bisa dipakai untuk scan.",
            ], 422);
        }

        $binding = DB::table('tag_garment_bindings')
            ->where('tag_id', $tag->tag_id)
            ->where('is_current', true)
            ->first();

        if (! $binding) {
            return response()->json([
                'message' => 'Tag ini tidak sedang terpasang pada baju manapun (belum ada binding aktif).',
            ], 422);
        }

        $garment = DB::table('garments')->where('garment_id', $binding->garment_id)->first();

        if (! $garment) {
            return response()->json(['message' => 'Data garment tidak ditemukan.'], 422);
        }

        $division = DB::table('divisions')->where('division_id', $garment->division_id)->first();
        $divisionName = $division->division_name ?? '-';

        if ($garment->status !== 'active') {
            return response()->json([
                'message' => "Garment berstatus '{$garment->status}', scan ditolak.",
                'garment_code' => $garment->garment_code,
                'division_name' => $divisionName,
            ], 422);
        }

        // ── Auto-detect event_type dari current_stage ──────────────────
        $stage = $garment->current_stage ?? 'siap_digunakan';
        if (! array_key_exists($stage, self::STAGE_FLOW)) {
            $stage = 'siap_digunakan';
        }

        $flow      = self::STAGE_FLOW[$stage];
        $eventType = $flow['event'];
        $nextStage = $flow['next_stage'];
        // ──────────────────────────────────────────────────────────────

        // ── ANTI STRAY-READ: cek minimal jarak waktu sejak stage terakhir berubah ──
        $minDwellMinutes = self::MIN_DWELL_MINUTES[$stage] ?? 0;

        if ($minDwellMinutes > 0 && $garment->stage_changed_at) {
            $stageChangedAt = Carbon::parse($garment->stage_changed_at);
            $elapsedMinutes = $stageChangedAt->diffInMinutes(now());

            if ($elapsedMinutes < $minDwellMinutes) {
                $sisaMenit = $minDwellMinutes - $elapsedMinutes;
                $sisaLabel = $sisaMenit >= 60
                    ? round($sisaMenit / 60, 1) . ' jam lagi'
                    : $sisaMenit . ' menit lagi';

                return response()->json([
                    'message'          => "{$garment->garment_code} {$sisaLabel}",
                    'garment_code'     => $garment->garment_code,
                    'division_name'    => $divisionName,
                    'current_stage'    => $stage,
                    'elapsed_minutes'  => $elapsedMinutes,
                    'required_minutes' => $minDwellMinutes,
                ], 422);
            }
        }
        // ─────────────────────────────────────────────────────────────────────────

        $isCompletingCycle = ($eventType === self::CYCLE_COMPLETING_EVENT);

        $cycleCountAfter = $isCompletingCycle
            ? $garment->current_cycle_count + 1
            : $garment->current_cycle_count;

        $scanEvent = DB::transaction(function () use ($tag, $garment, $device, $data, $cycleCountAfter, $eventType, $nextStage) {
            $event = ScanEvent::create([
                'tag_id'            => $tag->tag_id,
                'garment_id'        => $garment->garment_id,
                'session_id'        => $data['session_id'] ?? null,
                'device_id'         => $device->device_id,
                'event_type'        => $eventType,
                'scan_timestamp'    => $data['scanned_at'] ?? now(),
                'cycle_count_after' => $cycleCountAfter,
                'location'          => $data['location'] ?? $device->location,
            ]);

            DB::table('garments')
                ->where('garment_id', $garment->garment_id)
                ->update([
                    'current_stage'    => $nextStage,
                    'stage_changed_at' => now(),
                    'updated_at'       => now(),
                ]);

            return $event;
        });

        $thresholdStatus = 'ok';

        if ($isCompletingCycle) {
            $thresholdStatus = $this->checkCycleThreshold($garment, $tag, $device, $cycleCountAfter);
        }

        return response()->json([
            'message' => 'Scan berhasil dicatat.',
            'mode'    => 'operational',
            'data' => [
                'event_id'          => $scanEvent->event_id,
                'garment_code'      => $garment->garment_code,
                'division_name'     => $divisionName,
                'tag_uid'           => $tag->tag_uid,
                'event_type'        => $scanEvent->event_type,
                'cycle_count_after' => $cycleCountAfter,
                'max_cycle_limit'   => $garment->max_cycle_limit,
                'scan_timestamp'    => $scanEvent->scan_timestamp,
                'new_stage'         => $nextStage,
                'threshold_status'  => $thresholdStatus,
            ],
        ], 201);
    }

    public function startListening()
    {
        Cache::put(self::REGISTRATION_MODE_KEY, true, now()->addSeconds(60));
        Cache::forget(self::PENDING_TAG_KEY);

        return response()->json(['message' => 'Menunggu scan dari reader mana pun...']);
    }

    public function pollScan()
    {
        $pending = Cache::pull(self::PENDING_TAG_KEY);

        return response()->json([
            'tag_uid'     => $pending['tag_uid'] ?? null,
            'device_id'   => $pending['device_id'] ?? null,
            'device_name' => $pending['device_name'] ?? null,
        ]);
    }

    public function stopListening()
    {
        Cache::forget(self::REGISTRATION_MODE_KEY);
        Cache::forget(self::PENDING_TAG_KEY);

        return response()->json(['message' => 'Mode registrasi dihentikan']);
    }

    private function checkCycleThreshold(object $garment, Tag $tag, Device $device, int $cycleCountAfter): string
    {
        try {
            $limit = $garment->max_cycle_limit;
            $ratio = $limit > 0 ? $cycleCountAfter / $limit : 0;

            if ($cycleCountAfter >= $limit) {
                Alert::create([
                    'garment_id' => $garment->garment_id,
                    'tag_id'     => $tag->tag_id,
                    'device_id'  => $device->device_id,
                    'alert_type' => 'cycle_exceeded',
                    'severity'   => 'critical',
                    'message'    => "Garment {$garment->garment_code} telah mencapai/melebihi batas maksimal siklus autoclave ({$cycleCountAfter}/{$limit}).",
                ]);

                return 'critical';
            }

            if ($ratio >= self::WARNING_THRESHOLD_RATIO) {
                Alert::create([
                    'garment_id' => $garment->garment_id,
                    'tag_id'     => $tag->tag_id,
                    'device_id'  => $device->device_id,
                    'alert_type' => 'cycle_warning',
                    'severity'   => 'warning',
                    'message'    => "Garment {$garment->garment_code} mendekati batas maksimal siklus autoclave ({$cycleCountAfter}/{$limit}).",
                ]);

                return 'warning';
            }

            return 'ok';
        } catch (\Throwable $e) {
            Log::error('Gagal membuat alert threshold siklus: ' . $e->getMessage());

            return 'ok';
        }
    }
}
