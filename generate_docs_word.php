<?php

require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\SimpleType\JcTable;
use PhpOffice\PhpWord\Style\ListItem;

$phpWord = new PhpWord();

// Set default font
$phpWord->setDefaultFontName('Segoe UI');
$phpWord->setDefaultFontSize(10.5);

// Document Info
$docInfo = $phpWord->getDocInfo();
$docInfo->setCreator('AlfarFID Development Team');
$docInfo->setTitle('Dokumentasi Sistem AlfarFID Smart Cleanroom');
$docInfo->setSubject('Sistem Pelacakan Garment & Sterilisasi RFID UHF');
$docInfo->setDescription('Dokumentasi arsitektur, database, alur stage, modul web, dan protokol IoT');

// Title styles
$phpWord->addTitleStyle(1, ['name' => 'Segoe UI', 'size' => 16, 'bold' => true, 'color' => 'B85D30'], ['spaceBefore' => 240, 'spaceAfter' => 120]);
$phpWord->addTitleStyle(2, ['name' => 'Segoe UI', 'size' => 13, 'bold' => true, 'color' => '1E293B'], ['spaceBefore' => 180, 'spaceAfter' => 80]);
$phpWord->addTitleStyle(3, ['name' => 'Segoe UI', 'size' => 11, 'bold' => true, 'color' => '334155'], ['spaceBefore' => 120, 'spaceAfter' => 60]);

// Table styles
$tableStyle = [
    'borderColor' => 'CBD5E1',
    'borderSize'  => 6,
    'cellMargin'  => 100,
    'alignment'   => JcTable::CENTER,
];
$phpWord->addTableStyle('ModernTable', $tableStyle);

$codeBoxStyle = [
    'borderColor' => 'E2E8F0',
    'borderSize'  => 6,
    'cellMargin'  => 120,
    'alignment'   => JcTable::CENTER,
];
$phpWord->addTableStyle('CodeTable', $codeBoxStyle);

$headerCellStyle = ['bgColor' => 'F1F5F9', 'valign' => 'center'];
$headerFontStyle = ['bold' => true, 'color' => '1E293B', 'size' => 9.5];
$bodyCellStyle   = ['valign' => 'top'];
$bodyFontStyle   = ['size' => 9.5, 'color' => '334155'];
$codeFontStyle   = ['name' => 'Consolas', 'size' => 9, 'color' => 'B85D30'];

// Section configuration
$section = $phpWord->addSection([
    'marginTop'    => 1440,
    'marginBottom' => 1440,
    'marginLeft'   => 1440,
    'marginRight'  => 1440,
]);

// Header & Footer
$header = $section->addHeader();
$header->addText('AlfarFID Smart Cleanroom — Dokumentasi Sistem', ['size' => 8.5, 'color' => '94A3B8'], ['alignment' => Jc::RIGHT]);

$footer = $section->addFooter();
$footer->addPreserveText('Halaman {PAGE} dari {NUMPAGES}', ['size' => 8.5, 'color' => '94A3B8'], ['alignment' => Jc::CENTER]);

// ── TITLE / COVER ──
$section->addText('DOKUMENTASI SISTEM RESMI', ['name' => 'Segoe UI', 'size' => 11, 'bold' => true, 'color' => 'B85D30'], ['spaceAfter' => 40]);
$section->addText('AlfarFID Smart Cleanroom', ['name' => 'Segoe UI', 'size' => 22, 'bold' => true, 'color' => '0F172A'], ['spaceAfter' => 40]);
$section->addText('Centralized RFID Garment Tracking, Sterilization Lifecycle & IoT Monitoring Platform', ['name' => 'Segoe UI', 'size' => 11, 'italic' => true, 'color' => '64748B'], ['spaceAfter' => 180]);

$dividerTable = $section->addTable(['alignment' => JcTable::CENTER]);
$dividerTable->addRow();
$dividerTable->addCell(9000, ['bgColor' => 'B85D30'])->addText('', ['size' => 2]);
$section->addTextBreak(1);

// ── 1. TENTANG SISTEM ──
$section->addTitle('1. Tentang Sistem', 1);
$section->addText(
    'AlfarFID Smart Cleanroom adalah platform perangkat lunak dan Internet of Things (IoT) terintegrasi yang dirancang khusus untuk memantau, mengelola, dan melacak pergerakan serta siklus hidup pakaian kerja steril (cleanroom garments) secara otomatis menggunakan teknologi RFID UHF (Ultra High Frequency).',
    ['color' => '334155']
);
$section->addText(
    'Di industri farmasi, medis, dan manufaktur semikonduktor dengan standar ketat (seperti cGMP dan ISO 14644), integritas partikel dan sterilitas pakaian kerja adalah parameter vital. AlfarFID hadir untuk mengotomatisasi pengawasan siklus pakaian dari pencucian, sterilisasi autoclave, penyimpanan ruang steril, hingga pemakaian oleh operator.',
    ['color' => '334155']
);

$section->addText('Tantangan & Solusi yang Dihadirkan:', ['bold' => true, 'color' => '1E293B'], ['spaceBefore' => 100]);

$bulletStyle = ['listType' => ListItem::TYPE_BULLET_FILLED];
$section->addListItem('Pelacakan Batas Toleransi Sterilisasi: Mencegah penggunaan pakaian yang telah melebihi batas maksimal siklus autoclave (misal 50 siklus) demi menjaga kerapatan serat kain.', 0, null, $bulletStyle);
$section->addListItem('Pemisahan Siklus Baju vs Tag: Tag RFID silikon tahan hingga 200 siklus, sedangkan baju umumnya bertahan 50 siklus. Sistem mendukung alur Pindah Tag untuk menghemat anggaran pengadaan.', 0, null, $bulletStyle);
$section->addListItem('Pencegahan Human Error & Stray-Read: Dilengkapi algoritma Anti Stray-Read (Dwell Time) untuk menolak pembacaan RFID tidak sengaja di dekat reader.', 0, null, $bulletStyle);
$section->addListItem('Kepatuhan Regulasi & Audit Trail: Seluruh riwayat transaksi data (INSERT, UPDATE, DELETE) tercatat otomatis dengan stempel waktu mikrodetik dan riwayat perubahan nilai.', 0, null, $bulletStyle);

// ── 2. ARSITEKTUR & TECH STACK ──
$section->addTitle('2. Arsitektur & Tech Stack', 1);
$section->addText('Sistem AlfarFID terdiri dari dua komponen utama: Perangkat Keras (Hardware Reader IoT) dan Perangkat Lunak (Web Application Server).', ['color' => '334155']);

$table = $section->addTable('ModernTable');
$table->addRow();
$table->addCell(2500, $headerCellStyle)->addText('Komponen', $headerFontStyle);
$table->addCell(3000, $headerCellStyle)->addText('Teknologi / Modul', $headerFontStyle);
$table->addCell(3500, $headerCellStyle)->addText('Peran & Fungsi', $headerFontStyle);

$dataStack = [
    ['Backend Framework', 'Laravel 10.x (PHP 8.1 / 8.2)', 'REST API, Business Logic, Web Portal, Auth, dan Scheduler'],
    ['Database', 'MySQL 8.0 / MariaDB', 'Penyimpanan terstruktur dengan UUID PK, Triggers, Views & Stored Procedures'],
    ['Frontend UI', 'Tailwind CSS & Alpine.js', 'Antarmuka responsif modern Cleanroom Theme dan manipulasi DOM ringan'],
    ['Visualisasi Grafik', 'Chart.js', 'Grafik tren scan harian/mingguan dan distribusi keausan siklus'],
    ['Microcontroller', 'ESP32 NodeMCU 32-Bit', 'Kontroler pembacaan RFID, konektivitas WiFi HTTP, dan display TFT'],
    ['RFID Reader', 'UHF RFID Reader Module (UART)', 'Pembacaan tag RFID UHF jarak 0.5 - 2 meter secara nirkabel'],
    ['Display Terminal', 'TFT LCD SPI ILI9341 2.8"', 'Tampilan visual status baju, hasil scan, dan peringatan di pos fisik'],
];

foreach ($dataStack as $row) {
    $table->addRow();
    $table->addCell(2500, $bodyCellStyle)->addText($row[0], ['bold' => true, 'size' => 9]);
    $table->addCell(3000, $bodyCellStyle)->addText($row[1], $bodyFontStyle);
    $table->addCell(3500, $bodyCellStyle)->addText($row[2], $bodyFontStyle);
}

// ── 3. STRUKTUR BASIS DATA & ENTITAS UTAMA ──
$section->addTitle('3. Struktur Basis Data & Entitas Utama', 1);
$section->addText('Basis data AlfarFID menggunakan arsitektur UUID Primary Key (CHAR 36) untuk mendukung replikasi dan integritas data multi-plant. Berikut adalah entitas utama:', ['color' => '334155']);

$tableDb = $section->addTable('ModernTable');
$tableDb->addRow();
$tableDb->addCell(2200, $headerCellStyle)->addText('Nama Tabel', $headerFontStyle);
$tableDb->addCell(1600, $headerCellStyle)->addText('Kunci Utama', $headerFontStyle);
$tableDb->addCell(5200, $headerCellStyle)->addText('Deskripsi & Relasi Penting', $headerFontStyle);

$dataDb = [
    ['garments', 'garment_id', 'Menyimpan kode baju (GRM001), ukuran, divisi, status (active/in_repair/damaged/retired), stage terkini, dan total siklus.'],
    ['tags', 'tag_id', 'Menyimpan UID tag RFID fisik (tag_uid), total_cycles_used, rated_max_cycles (default 200), dan status.'],
    ['tag_garment_bindings', 'binding_id', 'Riwayat relasi many-to-many historis antara tag dan garment. Flag is_current menandai tag aktif.'],
    ['devices', 'device_id', 'Data reader ESP32, MAC address, hash API key, lokasi fisik, status online/offline, dan timestamp heartbeat.'],
    ['scan_events', 'event_id', 'Log transaksi pembacaan RFID: event_type, stempel waktu mikrodetik, cycle_count_after, dan relasi ke device & user.'],
    ['alerts', 'alert_id', 'Notifikasi sistem otomatis: cycle_warning (90%), cycle_exceeded (100%), device_offline, dan tag_wear_warning.'],
    ['divisions', 'division_id', 'Divisi operasional (Produksi Steril, QC, Packaging) lengkap dengan kode warna visual (color_hex).'],
    ['garment_categories', 'category_id', 'Kategori baju (Coverall, Hood, Booties) dan default batas siklus autoclave (default_max_cycle).'],
    ['audit_logs', 'log_id', 'Riwayat perubahan data: aksi (INSERT/UPDATE/DELETE), pelaku (changed_by), dan JSON diff nilai lama vs baru.'],
    ['users', 'user_id', 'Pengguna sistem dengan role-based authorization (admin, operator, viewer).'],
];

foreach ($dataDb as $row) {
    $tableDb->addRow();
    $tableDb->addCell(2200, $bodyCellStyle)->addText($row[0], $codeFontStyle);
    $tableDb->addCell(1600, $bodyCellStyle)->addText($row[1], ['size' => 8.5, 'color' => '64748B']);
    $tableDb->addCell(5200, $bodyCellStyle)->addText($row[2], $bodyFontStyle);
}

// ── 4. SIKLUS HIDUP PAKAIAN (STAGE FLOW) ──
$section->addTitle('4. Siklus Hidup Pakaian (State Machine & Stage Flow)', 1);
$section->addText(
    'AlfarFID menggunakan prinsip Server-Determined Stage Flow. Perangkat pembaca RFID (ESP32) tidak perlu menentukan jenis transaksi secara manual. Perangkat cukup mengirimkan UID tag yang terbaca, dan server secara otomatis menentukan transisi stage berdasarkan kondisi baju saat itu.',
    ['color' => '334155']
);

$tableStage = $section->addTable('ModernTable');
$tableStage->addRow();
$tableStage->addCell(2200, $headerCellStyle)->addText('Stage Asal', $headerFontStyle);
$tableStage->addCell(2200, $headerCellStyle)->addText('Event Scan', $headerFontStyle);
$tableStage->addCell(2200, $headerCellStyle)->addText('Stage Tujuan', $headerFontStyle);
$tableStage->addCell(1400, $headerCellStyle)->addText('Min Dwell', $headerFontStyle);
$tableStage->addCell(1000, $headerCellStyle)->addText('+Siklus', $headerFontStyle);

$dataStage = [
    ['siap_digunakan', 'usage_checkpoint', 'sedang_dipakai', '0 menit', 'Tidak'],
    ['sedang_dipakai', 'pre_autoclave', 'pre_autoclave', '9 jam (540m)', 'Tidak'],
    ['pre_autoclave', 'post_autoclave', 'siap_digunakan', '30 menit', '+1 (Ya)'],
];

foreach ($dataStage as $row) {
    $tableStage->addRow();
    $tableStage->addCell(2200, $bodyCellStyle)->addText($row[0], $codeFontStyle);
    $tableStage->addCell(2200, $bodyCellStyle)->addText($row[1], $bodyFontStyle);
    $tableStage->addCell(2200, $bodyCellStyle)->addText($row[2], $codeFontStyle);
    $tableStage->addCell(1400, $bodyCellStyle)->addText($row[3], $bodyFontStyle);
    $tableStage->addCell(1000, $bodyCellStyle)->addText($row[4], ['bold' => true, 'color' => ($row[4] === '+1 (Ya)' ? '047857' : '64748B')]);
}

$section->addTextBreak(1);
$section->addText('Mekanisme Proteksi Anti Stray-Read (Dwell Time):', ['bold' => true, 'color' => '1E293B']);
$section->addText(
    'Untuk mencegah salah baca ketika baju hanya melintas di dekat antena reader RFID, sistem memberlakukan batas waktu minimal keberadaan baju di setiap stage (Min Dwell). Bila scan diterima sebelum batas waktu terpenuhi, server akan menolak dengan pesan sisa waktu (misal: "GRM001 4.5 jam lagi").',
    ['color' => '334155']
);

// ── 5. MODUL & FITUR UTAMA APLIKASI ──
$section->addTitle('5. Modul & Fitur Utama Aplikasi', 1);

$section->addTitle('5.1 Dashboard & KPI Realtime', 2);
$section->addText('Menyajikan ringkasan metrik instan: Total Garment Aktif, Tag Aktif, Item Kritis (>=90% siklus), Scan Hari Ini, dan Status Reader Online. Dilengkapi tabel live feed aktivitas scan sterilisasi terakhir.');

$section->addTitle('5.2 Master Data Garment & Tag RFID', 2);
$section->addText('Menu konfigurasi lengkap untuk pakaian dan tag RFID:');
$section->addListItem('Auto-Generate Kode Garment: Pembuatan kode otomatis berurutan (GRM001, GRM002, dst) yang terkunci agar mencegah duplikasi.', 0, null, $bulletStyle);
$section->addListItem('Mode Registrasi RFID Global: Mode penangkapan scan nirkabel dari reader ESP32 mana pun untuk registrasi tag baru tanpa ketik manual.', 0, null, $bulletStyle);
$section->addListItem('Pagination & Sorting: Tabel data dilengkapi pagination 10 baris per halaman dan data terbaru tampil di posisi teratas.', 0, null, $bulletStyle);

$section->addTitle('5.3 Sirkulasi Pintar: Pindah Tag vs Ganti Tag', 2);
$section->addText('Mengakomodasi perbedaan masa pakai antara kain baju dan tag RFID:');
$section->addListItem('Pindah Tag (/admin/pindah-tag): Dijalankan ketika kain baju rusak/robek namun tag RFID masih memiliki sisa siklus. Tag dipindahkan ke baju baru yang belum bertag.', 0, null, $bulletStyle);
$section->addListItem('Ganti Tag (/admin/ganti-tag): Dijalankan ketika tag RFID aus/rusak namun kain baju masih bagus. Baju dipasangkan tag baru tanpa mereset riwayat siklus baju.', 0, null, $bulletStyle);

$section->addTitle('5.4 Early Warning System & Alerts', 2);
$section->addText('Sistem notifikasi proaktif untuk mencegah risiko kegagalan sterilisasi:');
$section->addListItem('Warning Alert (>=90% batas siklus): Memberikan aba-aba persiapan pengadaan.', 0, null, $bulletStyle);
$section->addListItem('Critical Alert (>=100% batas siklus): Notifikasi merah berdenyut; pakaian wajib segera dipensiunkan (retired).', 0, null, $bulletStyle);
$section->addListItem('Alur Penanganan: Tombol Acknowledge (diketahui operator) dan Resolve (diselesaikan dengan catatan petugas & stempel waktu).', 0, null, $bulletStyle);

$section->addTitle('5.5 Analytics & Prediksi Waktu Pensiun', 2);
$section->addText('Menyediakan grafik tren pembacaan RFID harian/mingguan, distribusi umur baju, distribusi keausan tag RFID silikon, serta prediksi jumlah baju yang akan retired dalam 7 dan 14 hari ke depan berdasarkan frekuensi penggunaan rata-rata.');

$section->addTitle('5.6 Audit Trail Terintegrasi', 2);
$section->addText('Mencatat seluruh rekam jejak manipulasi data (INSERT, UPDATE, DELETE) pada seluruh tabel master, lengkap dengan panel ekspansi perbandingan nilai lama vs nilai baru (JSON diff) dan filter pencarian berparameter.');

$section->addTitle('5.7 Manajemen Perangkat IoT & API Key', 2);
$section->addText('Mendaftarkan unit ESP32 reader baru, menetapkan lokasi fisik, memantau detak jantung (heartbeat), serta mengelola kunci API rahasia (af_dev_...) lengkap dengan fitur Regenerate Key.');

// ── 6. PROTOKOL INTEGRASI IOT (ESP32) ──
$section->addTitle('6. Protokol Integrasi IoT (ESP32 RFID Reader)', 1);
$section->addText('Perangkat ESP32 berkomunikasi dengan server Laravel menggunakan format REST API JSON standar HTTP/HTTPS.', ['color' => '334155']);

$section->addText('1. HTTP Header Autentikasi Wajib:', ['bold' => true, 'color' => '1E293B']);
$codeBox = $section->addTable('CodeTable');
$codeBox->addRow();
$cell1 = $codeBox->addCell(9000, ['bgColor' => 'F8FAFC']);
$cell1->addText('POST /scan-events HTTP/1.1', ['name' => 'Consolas', 'size' => 8.5, 'color' => '0F172A']);
$cell1->addText('Host: 192.168.1.106:8000', ['name' => 'Consolas', 'size' => 8.5, 'color' => '0F172A']);
$cell1->addText('Content-Type: application/json', ['name' => 'Consolas', 'size' => 8.5, 'color' => '0F172A']);
$cell1->addText('X-Device-Mac: 24:6F:28:AB:CD:EF', ['name' => 'Consolas', 'size' => 8.5, 'color' => '0F172A']);
$cell1->addText('X-Api-Key: af_dev_7a9f8b2c4e6d1a3b5c7e9f0a', ['name' => 'Consolas', 'size' => 8.5, 'color' => '0F172A']);

$section->addTextBreak(1);
$section->addText('2. Request Payload JSON:', ['bold' => true, 'color' => '1E293B']);
$codeBox2 = $section->addTable('CodeTable');
$codeBox2->addRow();
$cell2 = $codeBox2->addCell(9000, ['bgColor' => 'F8FAFC']);
$cell2->addText('{', ['name' => 'Consolas', 'size' => 8.5, 'color' => '0F172A']);
$cell2->addText('  "tag_uid": "E2801170000002157A89BC12"', ['name' => 'Consolas', 'size' => 8.5, 'color' => '0F172A']);
$cell2->addText('}', ['name' => 'Consolas', 'size' => 8.5, 'color' => '0F172A']);

$section->addTextBreak(1);
$section->addText('3. Contoh Respons Sukses (HTTP 201 Created):', ['bold' => true, 'color' => '1E293B']);
$codeBox3 = $section->addTable('CodeTable');
$codeBox3->addRow();
$cell3 = $codeBox3->addCell(9000, ['bgColor' => 'F8FAFC']);
$cell3->addText('{', ['name' => 'Consolas', 'size' => 8.5, 'color' => '047857']);
$cell3->addText('  "message": "Scan berhasil dicatat.",', ['name' => 'Consolas', 'size' => 8.5, 'color' => '047857']);
$cell3->addText('  "mode": "operational",', ['name' => 'Consolas', 'size' => 8.5, 'color' => '047857']);
$cell3->addText('  "data": {', ['name' => 'Consolas', 'size' => 8.5, 'color' => '047857']);
$cell3->addText('    "event_id": "d7e1c4a2-4f8b-4c2e-9a1b-3c4d5e6f7a8b",', ['name' => 'Consolas', 'size' => 8.5, 'color' => '047857']);
$cell3->addText('    "garment_code": "GRM001",', ['name' => 'Consolas', 'size' => 8.5, 'color' => '047857']);
$cell3->addText('    "division_name": "Produksi Steril",', ['name' => 'Consolas', 'size' => 8.5, 'color' => '047857']);
$cell3->addText('    "tag_uid": "E2801170000002157A89BC12",', ['name' => 'Consolas', 'size' => 8.5, 'color' => '047857']);
$cell3->addText('    "event_type": "post_autoclave",', ['name' => 'Consolas', 'size' => 8.5, 'color' => '047857']);
$cell3->addText('    "cycle_count_after": 12,', ['name' => 'Consolas', 'size' => 8.5, 'color' => '047857']);
$cell3->addText('    "max_cycle_limit": 50,', ['name' => 'Consolas', 'size' => 8.5, 'color' => '047857']);
$cell3->addText('    "new_stage": "siap_digunakan",', ['name' => 'Consolas', 'size' => 8.5, 'color' => '047857']);
$cell3->addText('    "threshold_status": "ok"', ['name' => 'Consolas', 'size' => 8.5, 'color' => '047857']);
$cell3->addText('  }', ['name' => 'Consolas', 'size' => 8.5, 'color' => '047857']);
$cell3->addText('}', ['name' => 'Consolas', 'size' => 8.5, 'color' => '047857']);

// ── 7. PANDUAN INSTALASI & SETUP ──
$section->addTitle('7. Panduan Instalasi & Konfigurasi Server', 1);
$section->addText('Petunjuk menjalankan sistem di lingkungan server / lokal:', ['color' => '334155']);

$section->addListItem('Pastikan PHP >= 8.1 dan MySQL/MariaDB sudah aktif di XAMPP.', 0, null, $bulletStyle);
$section->addListItem('Jalankan composer install di folder proyek.', 0, null, $bulletStyle);
$section->addListItem('Sesuaikan konfigurasi koneksi database di file .env (DB_DATABASE=alfarfid).', 0, null, $bulletStyle);
$section->addListItem('Eksekusi migrasi database dan data awal: php artisan migrate --seed', 0, null, $bulletStyle);
$section->addListItem('Jalankan web server: php artisan serve --host=0.0.0.0 --port=8000', 0, null, $bulletStyle);

$section->addTextBreak(2);
$section->addText('--- Dokumen Resmi AlfarFID Smart Cleanroom System ---', ['size' => 8.5, 'italic' => true, 'color' => '94A3B8'], ['alignment' => Jc::CENTER]);

// Save document
$targetFile = __DIR__ . '/DOKUMENTASI_SISTEM.docx';
$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
$objWriter->save($targetFile);

echo "SUCCESS_GENERATED: " . $targetFile . "\n";
