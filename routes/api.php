<?php

use App\Http\Controllers\Api\ScanEventController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Endpoint ini dipanggil oleh ESP32 (lihat sendToLaravel() di firmware).
| Autentikasi device dilakukan lewat middleware 'esp.device', yang membaca
| header X-Device-Mac & X-Api-Key, mencocokkan ke tabel devices, lalu
| menaruh hasilnya di $request->attributes->get('device') supaya bisa
| dibaca oleh ScanEventController@store.
|
*/

Route::middleware('esp.device')->group(function () {
    Route::post('/scan-events', [ScanEventController::class, 'store']);
    Route::post('/scan_events', [ScanEventController::class, 'store']);
    Route::post('/scan-event', [ScanEventController::class, 'store']);
    Route::post('/scans', [ScanEventController::class, 'store']);
    Route::post('/scan', [ScanEventController::class, 'store']);
    Route::post('/v1/scan-events', [ScanEventController::class, 'store']);
    Route::post('/v1/scans', [ScanEventController::class, 'store']);
});

Route::get('/tag-registration/poll', [ScanEventController::class, 'pollScan']);
Route::post('/tag-registration/start', [ScanEventController::class, 'startListening']);
Route::post('/tag-registration/stop', [ScanEventController::class, 'stopListening']);

// Handshake & Auto-Discovery endpoint untuk ESP32 Alfa RFID
Route::get('/ping', function () {
    return response()->json([
        'status'  => 'ok',
        'app'     => 'alfa_rfid_server',
        'message' => 'ALFA RFID Server Online',
        'ip'      => request()->server('SERVER_ADDR', request()->ip()),
        'time'    => now()->toDateTimeString(),
    ]);
});
