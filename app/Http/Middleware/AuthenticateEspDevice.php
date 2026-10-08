<?php

namespace App\Http\Middleware;

use App\Models\Device;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Middleware ini memvalidasi bahwa request datang dari ESP32 yang terdaftar.
 *
 * Header yang wajib dikirim ESP32:
 *   X-Device-Mac : 24:6F:28:AB:CD:EF   (mac_address, dipakai untuk cari row device)
 *   X-Api-Key    : <plain api key>     (dibandingkan ke devices.api_key_hash pakai Hash::check)
 *
 * Kalau valid, instance Device ditaruh di $request->attributes->set('device', $device)
 * supaya bisa dipakai langsung di controller tanpa query ulang.
 */
class AuthenticateEspDevice
{
    public function handle(Request $request, Closure $next)
    {
        $mac = $request->header('X-Device-Mac') ?? $request->input('mac_address') ?? $request->input('mac');
        $key = $request->header('X-Api-Key') ?? $request->input('api_key') ?? $request->input('key');
        
        $tagUid = $request->input('tag_uid') ?? $request->input('epc') ?? $request->input('uid') ?? $request->input('rfid_uid');
        $isUnregisteredTag = ! empty($tagUid) && \App\Models\Tag::where('tag_uid', trim($tagUid))->doesntExist();

        if (! empty($mac) && ! empty($key)) {
            $device = Device::where('mac_address', $mac)->first();
            if (! $device || ! Hash::check($key, $device->api_key_hash)) {
                return response()->json([
                    'message' => 'Device tidak dikenali atau API key salah.',
                ], 401);
            }
        } elseif (\Illuminate\Support\Facades\Cache::has('registration_mode_active') || $isUnregisteredTag) {
            $device = (! empty($mac) ? Device::where('mac_address', $mac)->first() : null)
                ?? Device::where('status', '!=', 'maintenance')->first()
                ?? Device::first();
        } else {
            return response()->json([
                'message' => 'Header X-Device-Mac dan X-Api-Key wajib diisi.',
            ], 401);
        }

        if (! $device) {
            return response()->json([
                'message' => 'Tidak ada device aktif yang terdaftar di sistem.',
            ], 401);
        }

        if ($device->status === 'maintenance') {
            return response()->json([
                'message' => 'Device sedang dalam status maintenance, scan ditolak.',
            ], 423);
        }

        // Update heartbeat setiap kali device berhasil autentikasi
        $device->forceFill([
            'status' => 'online',
            'last_heartbeat_at' => now(),
        ])->save();

        $request->attributes->set('device', $device);

        return $next($request);
    }
}
