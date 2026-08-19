<?php

use Illuminate\Support\Facades\Route;

use Illuminate\Http\Request;
use App\Http\Controllers\AuditTrailController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\UserController;
//use App\Http\Controllers\GarmentController;
use App\Http\Controllers\GarmentController;
use App\Http\Controllers\GarmentConfigController;
use App\Http\Controllers\TagReassignmentController;
use App\Http\Controllers\ScanEventController;
use App\Http\Controllers\ScanHistoryReportController;
use App\Http\Controllers\AnalyticsController;
use App\Models\User;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/me', function (Request $request) {
        return $request->user();
    });

    Route::post('/change-password', [UserController::class, 'changeOwnPassword']);


    // Grup khusus admin
    Route::middleware('role:' . User::ROLE_ADMIN)
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {

            Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
                ->name('dashboard');
            // ================= GARMENT & TAG (FR-04) =================
            // Route::resource menghasilkan otomatis:
            // GET    admin/garments            -> admin.garments.index
            // GET    admin/garments/create      -> admin.garments.create
            // POST   admin/garments             -> admin.garments.store
            // GET    admin/garments/{id}        -> admin.garments.show
            // GET    admin/garments/{id}/edit    -> admin.garments.edit
            // PUT    admin/garments/{id}        -> admin.garments.update
            // DELETE admin/garments/{id}        -> admin.garments.destroy
            //Route::resource('garments', GarmentController::class);

            // Riwayat scan per item garment (dipanggil dari tombol "Riwayat Scan")
            // Route::get('garments/{garment}/history', [GarmentController::class, 'history'])
            //     ->name('garments.history');

            // Registrasi garment baru sekaligus pasang tag pertama kali (dari tab "Garment"
            // di halaman garment-config). Sengaja tidak pakai Route::resource di atas
            // (yang masih di-comment) supaya tidak bentrok/duplikat nama route.
            // Catatan: tag_id di form ini sekarang OPSIONAL — baju boleh dibuat dulu
            // tanpa tag, lalu dipasangi tag lewat menu Pindah Tag (lihat di bawah).
            Route::post('garments', [GarmentController::class, 'store'])
                ->name('garments.store');
            Route::delete('garments/{id}', [GarmentController::class, 'destroy'])
                ->name('garments.destroy');

            // ================= KONFIGURASI BAJU (Kategori & Divisi/Warna) =================
            Route::get('garment-config', [GarmentConfigController::class, 'index'])
                ->name('garment-config.index');

            // Sub-resource: Kategori Baju
            Route::post('garment-categories', [GarmentConfigController::class, 'storeCategory'])
                ->name('garment-categories.store');
            Route::put('garment-categories/{category}', [GarmentConfigController::class, 'updateCategory'])
                ->name('garment-categories.update');
            Route::delete('garment-categories/{category}', [GarmentConfigController::class, 'destroyCategory'])
                ->name('garment-categories.destroy');

            // Sub-resource: Divisi & Warna
            Route::post('divisions', [GarmentConfigController::class, 'storeDivision'])
                ->name('divisions.store');
            Route::put('divisions/{division}', [GarmentConfigController::class, 'updateDivision'])
                ->name('divisions.update');
            Route::delete('divisions/{division}', [GarmentConfigController::class, 'destroyDivision'])
                ->name('divisions.destroy');


            // ================= TAG REASSIGNMENT (FR-19) =================
            // Dulu 1 alur gabungan "tag-reassignment" yang mendeteksi skenario
            // otomatis. Sekarang dipecah jadi 2 alur eksplisit supaya admin
            // tidak salah pilih baju/tag:
            //
            //   Pindah Tag  — baju rusak, tag masih bagus. Tag lama dipindah
            //                 ke baju baru yang belum ada tag-nya.
            //   Ganti Tag   — baju masih bagus, tag aus. Baju ini dipasangi
            //                 tag fisik baru hasil scan (belum pernah terdaftar).
            //
            // Satu controller (TagReassignmentController), 4 method terpisah.
            Route::get('pindah-tag', [TagReassignmentController::class, 'indexPindah'])
                ->name('pindah-tag.index');
            Route::post('pindah-tag', [TagReassignmentController::class, 'storePindah'])
                ->name('pindah-tag.store');

            Route::get('ganti-tag', [TagReassignmentController::class, 'indexGanti'])
                ->name('ganti-tag.index');
            Route::post('ganti-tag', [TagReassignmentController::class, 'storeGanti'])
                ->name('ganti-tag.store');


            // ================= REGISTRASI TAG VIA SCAN RFID (GLOBAL) =================
            // Dipanggil dari modal "Input Tag Baru" — reader/ESP32 mana pun boleh
            // menangkap scan pertama selama mode ini aktif (lihat ScanEventController).
            Route::post('tag-registration/start', [\App\Http\Controllers\Api\ScanEventController::class, 'startListening'])
                ->name('tag-registration.start');
            Route::get('tag-registration/poll', [\App\Http\Controllers\Api\ScanEventController::class, 'pollScan'])
                ->name('tag-registration.poll');
            Route::post('tag-registration/stop', [\App\Http\Controllers\Api\ScanEventController::class, 'stopListening'])
                ->name('tag-registration.stop');



            // ================= TAG RFID =================
            Route::post('tags', [\App\Http\Controllers\TagController::class, 'store'])
                ->name('tags.store');
            Route::put('tags/{id}', [\App\Http\Controllers\TagController::class, 'update'])
                ->name('tags.update');
            Route::delete('tags/{id}', [\App\Http\Controllers\TagController::class, 'destroy'])
                ->name('tags.destroy');


            // ================= SCAN EVENTS (LOG AKTIVITAS SCAN) =================
            Route::get('scan-events', [ScanEventController::class, 'index'])
                ->name('scan-events.index');
            Route::get('scan-events/data', [ScanEventController::class, 'data'])
                ->name('scan-events.data');

            Route::get('analytics', [AnalyticsController::class, 'index'])
                ->name('analytics.index');

            Route::get('analytics/laporan/download', [\App\Http\Controllers\GarmentReportController::class, 'downloadGarmentStatusReport'])
                ->name('analytics.report.download');

            Route::get('/reports', [ScanHistoryReportController::class, 'index'])->name('reports.index');
            Route::get('/reports/data', [ScanHistoryReportController::class, 'data'])->name('reports.data');

            Route::get('/reports/reissue/{reportGenerated}', [ScanHistoryReportController::class, 'reissue'])
                ->name('reports.reissue');



            Route::get('/audit-trail', [AuditTrailController::class, 'index'])
                ->name('audit-trail.index');

            Route::resource('users', UserController::class);
        });

    // Grup khusus operator (kalau nanti sudah ada view/controllernya)
    Route::middleware('role:' . User::ROLE_OPERATOR)
        ->prefix('operator')
        ->name('operator.')
        ->group(function () {

            // Dashboard operator = halaman Scan Events, cuma beda layout/sidebar
            Route::get('/dashboard', [ScanEventController::class, 'index'])
                ->name('dashboard');

            Route::get('scan-events/data', [ScanEventController::class, 'data'])
                ->name('scan-events.data');
        });

    // Grup khusus viewer
    Route::middleware('role:' . User::ROLE_VIEWER)
        ->prefix('viewer')
        ->name('viewer.')
        ->group(function () {

            Route::get('/dashboard', function () {
                return view('viewer.dashboard');
            })->name('dashboard');
        });
});
