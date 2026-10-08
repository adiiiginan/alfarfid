<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Mengisi session variable MySQL @current_user_id di awal tiap request,
 * supaya trigger AFTER UPDATE/DELETE di database (lihat audit_triggers.sql)
 * tahu siapa user yang sedang login saat perubahan data terjadi.
 *
 * Kalau user belum login (mis. request dari API device RFID), variable
 * ini akan NULL — trigger tetap jalan, cuma changed_by-nya NULL.
 */
class SetAuditContext
{
    public function handle(Request $request, Closure $next)
    {
        DB::statement('SET @current_user_id = ?', [Auth::id()]);

        return $next($request);
    }
}
