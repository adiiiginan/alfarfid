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
});
