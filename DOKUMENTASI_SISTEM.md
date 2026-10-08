# Dokumentasi Sistem AlfarFID Smart Cleanroom
### *Centralized RFID Garment Tracking, Sterilization Lifecycle & IoT Monitoring System*

---

## 📌 Daftar Isi
1. [Tentang Sistem](#1-tentang-sistem)
2. [Arsitektur & Tech Stack](#2-arsitektur--tech-stack)
3. [Struktur Basis Data & Entitas Utama](#3-struktur-basis-data--entitas-utama)
4. [Siklus Hidup Pakaian (State Machine & Stage Flow)](#4-siklus-hidup-pakaian-state-machine--stage-flow)
5. [Modul & Fitur Utama Aplikasi](#5-modul--fitur-utama-aplikasi)
   - [5.1 Dashboard & KPI Realtime](#51-dashboard--kpi-realtime)
   - [5.2 Master Data Garment & Tag RFID](#52-master-data-garment--tag-rfid)
   - [5.3 Sirkulasi Pintar: Pindah Tag vs Ganti Tag](#53-sirkulasi-pintar-pindah-tag-vs-ganti-tag)
   - [5.4 Pelacakan Scan Realtime (Scan Events)](#54-pelacakan-scan-realtime-scan-events)
   - [5.5 Early Warning System & Alerts](#55-early-warning-system--alerts)
   - [5.6 Analytics, Tren & Prediksi Pensiun](#56-analytics-tren--prediksi-pensiun)
   - [5.7 Audit Trail Terintegrasi](#57-audit-trail-terintegrasi)
   - [5.8 Manajemen Perangkat IoT & API Key](#58-manajemen-perangkat-iot--api-key)
   - [5.9 Manajemen Pengguna & Hak Akses (RBAC)](#59-manajemen-pengguna--hak-akses-rbac)
   - [5.10 Laporan & Export Data](#510-laporan--export-data)
6. [Protokol Integrasi IoT (ESP32 RFID Reader)](#6-protokol-integrasi-iot-esp32-rfid-reader)
7. [Panduan Instalasi & Konfigurasi](#7-panduan-instalasi--konfigurasi)

---

## 1. Tentang Sistem

**AlfarFID Smart Cleanroom** adalah platform terintegrasi berbasis IoT dan web untuk mengelola, melacak, dan memantau siklus hidup pakaian kerja steril (*cleanroom garments*) secara otomatis menggunakan teknologi **RFID UHF**. 

### Masalah yang Diselesaikan:
- **Pelacakan Siklus Autoclave:** Memastikan setiap baju tidak melebihi batas toleransi sterilisasi panas uap (*max cycle limit*, misal 50 siklus) demi menjaga integritas partikel kain cleanroom.
- **Pemisahan Umur Tag vs Baju:** Baju memiliki umur ±50 siklus, sedangkan Tag RFID silikon tahan hingga ±200 siklus. Sistem memungkinkan pemanfaatan tag secara optimal lintas beberapa generasi baju.
- **Pencegahan Human Error & Stray-Read:** Mekanisme *Anti Stray-Read (Dwell Time)* otomatis menolak pembacaan tag tidak sengaja.
- **Kepatuhan Audit & Standar Farmasi (cGMP / ISO 14644):** Riwayat lengkap perpindahan baju, stempel waktu mikrodetik, dan log perubahan data (*Audit Trail*) tercatat rapi.

---

## 2. Arsitektur & Tech Stack

```
+-------------------------------------------------------------------------+
|                          PERANGKAT KERAS (HARDWARE)                     |
|  [ESP32 Microcontroller] <--> [UHF RFID Reader Module] <--> [TFT ILI9341] |
+-------------------------------------------------------------------------+
                                    |
                           (HTTP POST / JSON)
                      (Header: X-Device-Mac & X-Api-Key)
                                    v
+-------------------------------------------------------------------------+
|                        BACKEND & APPLICATION LAYER                      |
|  - Framework      : Laravel 10 (PHP 8.2+)                               |
|  - Database       : MySQL / MariaDB (UUID Primary Keys, Triggers, Views)|
|  - Cache & State  : File / Redis Cache (Mode Registrasi RFID)           |
|  - Frontend UI    : Tailwind CSS + Alpine.js + Chart.js + Blade         |
+-------------------------------------------------------------------------+
```

### Rincian Teknologi:
- **Backend:** Laravel 10.x, PHP 8.1 / 8.2
- **Database:** MySQL 8.0 / MariaDB 10.4+ (menggunakan UUID CHAR(36), Stored Procedures, dan Views)
- **Frontend:** Blade Templates, Tailwind CSS (Cleanroom Theme Palette), Alpine.js, Chart.js
- **Hardware Reader:** ESP32 NodeMCU, UHF RFID Reader (UART/Serial), Display TFT LCD SPI ILI9341 2.8"

---

## 3. Struktur Basis Data & Entitas Utama

Berikut adalah tabel-tabel utama di dalam database:

| Nama Tabel | Deskripsi | Kunci Utama | Keterangan Relasi |
| :--- | :--- | :--- | :--- |
| `garments` | Data fisik baju cleanroom | `garment_id` (UUID) | Relasi ke `category_id`, `division_id`, `current_tag_id` |
| `tags` | Master tag RFID UHF | `tag_id` (UUID) | Menyimpan `tag_uid`, `total_cycles_used`, `rated_max_cycles` |
| `tag_garment_bindings` | Riwayat pemasangan tag ke baju | `binding_id` (UUID) | Menyimpan `is_current` (aktif/lepas), `bound_at`, `unbound_at` |
| `devices` | Perangkat reader ESP32 | `device_id` (UUID) | Menyimpan `mac_address`, `api_key_hash`, `status`, `last_heartbeat` |
| `scan_events` | Log pembacaan RFID realtime | `event_id` (UUID) | Menyimpan `event_type`, `scan_timestamp`, `cycle_count_after` |
| `alerts` | Peringatan & notifikasi sistem | `alert_id` (UUID) | Kategori: `cycle_warning`, `cycle_exceeded`, `device_offline` |
| `divisions` | Divisi & kode warna baju | `division_id` (UUID) | Menentukan warna kain baju dan unit kerja |
| `garment_categories` | Jenis kategori baju | `category_id` (UUID) | Coverall, Hood, Booties, Jumpsuit, dsb. |
| `audit_logs` | Log perubahan data (Audit Trail) | `log_id` (UUID) | Menyimpan diff JSON nilai lama vs nilai baru |
| `users` | Akun pengguna web | `user_id` (UUID) | Role: `admin`, `operator`, `viewer` |

---

## 4. Siklus Hidup Pakaian (State Machine & Stage Flow)

AlfarFID menggunakan konsep **Server-Determined Stage Flow**. Alat pembaca RFID (ESP32) hanya perlu mengirimkan UID tag yang terbaca, dan server secara otomatis menentukan tahap berikutnya berdasarkan status terkini garment:

```mermaid
stateDiagram-v2
    [*] --> siap_digunakan: Pendaftaran Baju & Pasang Tag
    
    siap_digunakan --> sedang_dipakai: Scan di Gowning Room (usage_checkpoint)
    sedang_dipakai --> pre_autoclave: Scan saat Baju Kotor Masuk Laundry (pre_autoclave)
    pre_autoclave --> siap_digunakan: Scan Selesai Sterilisasi (post_autoclave)
    
    note right of post_autoclave
      Hanya event post_autoclave
      yang menambah cycle_count baju (+1)
      dan total_cycles_used tag (+1)
    end note
```

### Tabel Transisi Stage & Proteksi Anti Stray-Read:
| Stage Saat Ini | Event Otomatis | Stage Berikutnya | Syarat Minimal Waktu (*Min Dwell*) | Siklus Bertambah? |
| :--- | :--- | :--- | :--- | :---: |
| `siap_digunakan` | `usage_checkpoint` | `sedang_dipakai` | Bebas (0 menit) | Tidak |
| `sedang_dipakai` | `pre_autoclave` | `pre_autoclave` | Minimal 9 jam (540 menit) | Tidak |
| `pre_autoclave` | `post_autoclave` | `siap_digunakan` | Minimal 30 menit (autoclave) | **Ya (+1)** |

> **Catatan:** Jika tag ter-scan sebelum durasi *min dwell* terpenuhi, server akan menolak dengan HTTP 422 dan menampilkan sisa waktu tunggu (contoh: *"GRM002 4.5 jam lagi"*).

---

## 5. Modul & Fitur Utama Aplikasi

### 5.1 Dashboard & KPI Realtime
- Ringkasan KPI: **Garment Aktif**, **Tag Aktif**, **Item Kritis (>=90%)**, **Scan Hari Ini**, dan **Status Reader Online**.
- Status perangkat IoT multi-fasilitas (Bandung, Surabaya, dsb).
- Live feed 8 aktivitas scan sterilisasi terakhir.

### 5.2 Master Data Garment & Tag RFID
- Halaman konfigurasi: `/admin/garment-config`.
- **Auto Generate Kode Garment:** Format sequential otomatis `GRM001`, `GRM002`, dst (terkunci/read-only saat tambah data).
- **Tab Garment:** Tabel daftar baju lengkap dengan pagination, filter status, dan penanda warna divisi.
- **Tab Tag RFID:** Tabel daftar tag fisik UHF beserta sisa batas siklus (*rated max cycles*).
- **Mode Registrasi Tag RFID Global:** Menangkap pembacaan tag baru secara nirkabel langsung dari reader fisik manapun tanpa ketik manual.

### 5.3 Sirkulasi Pintar: Pindah Tag vs Ganti Tag
Untuk menghemat biaya operasional, sistem memisahkan dua alur:
1. **Pindah Tag (`/admin/pindah-tag`):**
   - *Kasus:* Baju robek/rusak, tetapi tag RFID masih bagus (sisa siklus masih banyak).
   - *Aksi:* Tag lama dilepas dari baju rusak, lalu dipasangkan ke baju baru yang belum memiliki tag.
2. **Ganti Tag (`/admin/ganti-tag`):**
   - *Kasus:* Tag RFID aus/rusak, tetapi kain baju masih dalam kondisi prima.
   - *Aksi:* Baju lama dipasangkan tag fisik baru tanpa mereset riwayat siklus baju yang sudah berjalan.

### 5.4 Pelacakan Scan Realtime (Scan Events)
- Halaman: `/admin/scan-events`.
- Menampilkan seluruh log pembacaan RFID lengkap dengan nama reader, lokasi, stempel waktu, jenis event, dan nomor siklus ke-N.
- Dilengkapi polling AJAX realtime untuk memperbarui data tanpa reload halaman.

### 5.5 Early Warning System & Alerts
- Halaman: `/admin/alerts`.
- Mendeteksi otomatis ketika baju atau tag mendekati atau melebihi batas siklus maksimal:
  - **Warning (>=90%):** Peringatan kuning untuk persiapan pengadaan.
  - **Critical (>=100%):** Notifikasi merah berdenyut; baju harus segera dipensiunkan (*retired*).
- Fitur penanganan: **Acknowledge** (ditandai dibaca) dan **Resolve** (diselesaikan oleh petugas).

### 5.6 Analytics, Tren & Prediksi Pensiun
- Halaman: `/admin/analytics`.
- **Grafik Tren Scan:** Jumlah scan harian/mingguan.
- **Distribusi Umur Baju:** Pengelompokan umur pakai pakaian cleanroom.
- **Distribusi Keausan Tag:** Monitoring keausan tag RFID silikon (terpisah dari baju).
- **Prediksi Waktu Pensiun:** Estimasi berapa banyak baju yang akan habis masa pakainya dalam 7 hari & 14 hari ke depan berdasarkan frekuensi sterilisasi rata-rata.
- **Rekomendasi Pengadaan:** Rekomendasi kuantitas pembelian baju/tag baru.

### 5.7 Audit Trail Terintegrasi
- Halaman: `/admin/audit-trail`.
- Pencatatan seluruh aksi `INSERT`, `UPDATE`, dan `DELETE` pada master data.
- Menampilkan accordion perbandingan nilai lama vs nilai baru (*JSON diff*).
- Filter pencarian berdasarkan nama tabel, aksi, pengguna, dan rentang tanggal.

### 5.8 Manajemen Perangkat IoT & API Key
- Halaman: `/admin/devices`.
- Mendaftarkan reader ESP32 baru, menetapkan nama perangkat dan lokasi fisik.
- Generator API Key otomatis (`af_dev_...`) dan tombol **Regenerate API Key**.
- Monitoring *Heartbeat* dan indikator status *Online/Offline/Maintenance*.

### 5.9 Manajemen Pengguna & Hak Akses (RBAC)
- Tiga tingkatan hak akses:
  1. **Admin:** Akses penuh seluruh konfigurasi, master data, user management, dan audit trail.
  2. **Operator:** Akses khusus ke dashboard operasional dan monitoring scan events.
  3. **Viewer:** Akses read-only untuk kebutuhan monitoring eksekutif / auditor.

### 5.10 Laporan & Export Data
- Export laporan riwayat scan per periode waktu.
- Download laporan status garment bulanan dalam format **PDF**.

---

## 6. Protokol Integrasi IoT (ESP32 RFID Reader)

Perangkat ESP32 berkomunikasi dengan server melalui REST API JSON via koneksi WiFi/LAN.

### 1. Header Autentikasi Wajib:
```http
POST /scan-events HTTP/1.1
Host: 192.168.1.106:8000
Content-Type: application/json
X-Device-Mac: 24:6F:28:AB:CD:EF
X-Api-Key: af_dev_xxxxxxxxxxxxxxxxxxxxxxxx
```

### 2. Request Body:
```json
{
  "tag_uid": "E2801170000002157A89BC12"
}
```

### 3. Response Berhasil (HTTP 201):
```json
{
  "message": "Scan berhasil dicatat.",
  "mode": "operational",
  "data": {
    "event_id": "9b1c7f8a-...",
    "garment_code": "GRM001",
    "division_name": "Produksi Steril",
    "tag_uid": "E2801170000002157A89BC12",
    "event_type": "post_autoclave",
    "cycle_count_after": 12,
    "max_cycle_limit": 50,
    "new_stage": "siap_digunakan",
    "threshold_status": "ok"
  }
}
```

### 4. Response Ditolak Dwell Time (HTTP 422):
```json
{
  "message": "GRM001 5.2 jam lagi",
  "garment_code": "GRM001",
  "division_name": "Produksi Steril",
  "current_stage": "sedang_dipakai",
  "elapsed_minutes": 228,
  "required_minutes": 540
}
```

---

## 7. Panduan Instalasi & Konfigurasi

### Kebutuhan Server:
- Web Server (Apache / Nginx) atau XAMPP
- PHP >= 8.1 (Ekstensi: `pdo_mysql`, `bcmath`, `mbstring`, `json`, `curl`, `gd`)
- MySQL >= 8.0 atau MariaDB >= 10.4
- Composer 2.x

### Langkah Instalasi:
1. **Clone / Buka Proyek:**
   ```bash
   cd c:\xampp\htdocs\alfarfid
   ```
2. **Install Dependensi:**
   ```bash
   composer install
   ```
3. **Konfigurasi Environment (`.env`):**
   ```ini
   APP_NAME="AlfarFID Smart Cleanroom"
   APP_ENV=local
   APP_KEY=base64:...
   APP_DEBUG=true
   APP_URL=http://192.168.1.106:8000

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=alfarfid
   DB_USERNAME=root
   DB_PASSWORD=
   ```
4. **Migrasi Database & Seeder:**
   ```bash
   php artisan migrate --seed
   ```
5. **Jalankan Server Lokal:**
   ```bash
   php artisan serve --host=0.0.0.0 --port=8000
   ```
   Akses aplikasi melalui browser: `http://localhost:8000` atau `http://192.168.1.106:8000`.

---
*Dokumentasi ini disusun dan diperbarui untuk versi AlfarFID Smart Cleanroom 2026.*
