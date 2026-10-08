<?php

namespace Database\Seeders;

use App\Models\Alert;
use App\Models\Device;
use App\Models\Division;
use App\Models\Garment;
use App\Models\GarmentCategory;
use App\Models\ScanEvent;
use App\Models\Tag;
use App\Models\TagGarmentBinding;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MultiLocationDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 1. USERS (Super Admin, Operator Bandung, Operator Surabaya, QA Supervisor)
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@alfarfid.local'],
            [
                'full_name'     => 'Super Admin Pusat',
                'password_hash' => Hash::make('admin'),
                'role'          => User::ROLE_ADMIN,
                'is_active'     => true,
            ]
        );

        $opBdg = User::firstOrCreate(
            ['email' => 'operator.bdg@alfarfid.local'],
            [
                'full_name'     => 'Ahmad Fauzi (Operator Bandung)',
                'password_hash' => Hash::make('password123'),
                'role'          => User::ROLE_OPERATOR,
                'is_active'     => true,
            ]
        );

        $opSby = User::firstOrCreate(
            ['email' => 'operator.sby@alfarfid.local'],
            [
                'full_name'     => 'Budi Santoso (Operator Surabaya)',
                'password_hash' => Hash::make('password123'),
                'role'          => User::ROLE_OPERATOR,
                'is_active'     => true,
            ]
        );

        $qaSupervisor = User::firstOrCreate(
            ['email' => 'qa.supervisor@alfarfid.local'],
            [
                'full_name'     => 'Dr. Hendra Wijaya (QA Supervisor)',
                'password_hash' => Hash::make('password123'),
                'role'          => User::ROLE_ADMIN,
                'is_active'     => true,
            ]
        );

        // 2. KATEGORI GARMENT
        $catCoverall = GarmentCategory::firstOrCreate(
            ['category_name' => 'Coverall Cleanroom Steril'],
            [
                'description'       => 'Pakaian pelindung menyeluruh kelas ISO 5 / Grade A',
                'default_max_cycle' => 50,
            ]
        );

        $catJasLab = GarmentCategory::firstOrCreate(
            ['category_name' => 'Jas Lab Steril Mikrobiologi'],
            [
                'description'       => 'Jas laboratorium untuk pengujian mikrobiologi steril',
                'default_max_cycle' => 60,
            ]
        );

        $catBedah = GarmentCategory::firstOrCreate(
            ['category_name' => 'Baju Bedah & Tindakan Steril'],
            [
                'description'       => 'Setelan baju bedah operasi khusus ruang steril',
                'default_max_cycle' => 40,
            ]
        );

        // 3. DIVISI & WARNA
        $divBdg = Division::firstOrCreate(
            ['division_name' => 'Produksi Steril - Pabrik Bandung'],
            [
                'color_name'  => 'Biru Safir',
                'color_hex'   => '#2563EB',
                'description' => 'Area produksi dan filling steril Pabrik Bandung',
                'is_active'   => true,
            ]
        );

        $divSby = Division::firstOrCreate(
            ['division_name' => 'Produksi Steril - Pabrik Surabaya'],
            [
                'color_name'  => 'Hijau Zamrud',
                'color_hex'   => '#059669',
                'description' => 'Area formulasi dan sterilisasi Pabrik Surabaya',
                'is_active'   => true,
            ]
        );

        $divQC = Division::firstOrCreate(
            ['division_name' => 'Quality Control & Mikrobiologi'],
            [
                'color_name'  => 'Kuning Amber',
                'color_hex'   => '#D97706',
                'description' => 'Laboratorium pengujian sterilitas antar-cabang',
                'is_active'   => true,
            ]
        );

        // 4. PERANGKAT READER RFID (BANDUNG & SURABAYA)
        $devBdgAutoclave = Device::firstOrCreate(
            ['mac_address' => '24:6F:28:BD:01:01'],
            [
                'device_name'       => 'RFID Reader Autoclave Line 1 (Bandung)',
                'location'          => 'Pabrik Bandung - Area Sterilisasi (Lt. 1)',
                'division_id'       => $divBdg->division_id,
                'api_key_hash'      => Hash::make('bandung_secret_key_1'),
                'status'            => 'online',
                'last_heartbeat_at' => now(),
                'installed_at'      => now()->subMonths(6),
            ]
        );

        $devBdgGowning = Device::firstOrCreate(
            ['mac_address' => '24:6F:28:BD:01:02'],
            [
                'device_name'       => 'RFID Reader Gowning Masuk (Bandung)',
                'location'          => 'Pabrik Bandung - Ruang Antara Masuk Cleanroom',
                'division_id'       => $divBdg->division_id,
                'api_key_hash'      => Hash::make('bandung_secret_key_2'),
                'status'            => 'online',
                'last_heartbeat_at' => now()->subMinutes(2),
                'installed_at'      => now()->subMonths(6),
            ]
        );

        $devSbyAutoclave = Device::firstOrCreate(
            ['mac_address' => '24:6F:28:SB:02:01'],
            [
                'device_name'       => 'RFID Reader Autoclave Line 2 (Surabaya)',
                'location'          => 'Pabrik Surabaya - Area Sterilisasi Sentral',
                'division_id'       => $divSby->division_id,
                'api_key_hash'      => Hash::make('surabaya_secret_key_1'),
                'status'            => 'online',
                'last_heartbeat_at' => now()->subMinutes(1),
                'installed_at'      => now()->subMonths(4),
            ]
        );

        $devSbyGowning = Device::firstOrCreate(
            ['mac_address' => '24:6F:28:SB:02:02'],
            [
                'device_name'       => 'RFID Reader Gowning Masuk (Surabaya)',
                'location'          => 'Pabrik Surabaya - Ruang Gowning Koridor Utama',
                'division_id'       => $divSby->division_id,
                'api_key_hash'      => Hash::make('surabaya_secret_key_2'),
                'status'            => 'online',
                'last_heartbeat_at' => now()->subMinutes(3),
                'installed_at'      => now()->subMonths(4),
            ]
        );

        // 5. DATA GARMENT & TAG (BANDUNG)
        $itemsBdg = [
            [
                'code'        => 'GAR-BDG-001',
                'size'        => 'L',
                'category'    => $catCoverall,
                'cycles'      => 14,
                'limit'       => 50,
                'stage'       => 'siap_digunakan',
                'tag_uid'     => 'E280111122223301',
                'status'      => 'active',
            ],
            [
                'code'        => 'GAR-BDG-002',
                'size'        => 'M',
                'category'    => $catCoverall,
                'cycles'      => 46, // Kritis (92%)
                'limit'       => 50,
                'stage'       => 'sedang_dipakai',
                'tag_uid'     => 'E280111122223302',
                'status'      => 'active',
            ],
            [
                'code'        => 'GAR-BDG-003',
                'size'        => 'XL',
                'category'    => $catJasLab,
                'cycles'      => 22,
                'limit'       => 60,
                'stage'       => 'pre_autoclave',
                'tag_uid'     => 'E280111122223303',
                'status'      => 'active',
            ],
            [
                'code'        => 'GAR-BDG-004',
                'size'        => 'M',
                'category'    => $catBedah,
                'cycles'      => 37, // Warning (92.5%)
                'limit'       => 40,
                'stage'       => 'siap_digunakan',
                'tag_uid'     => 'E280111122223304',
                'status'      => 'active',
            ],
        ];

        // 6. DATA GARMENT & TAG (SURABAYA)
        $itemsSby = [
            [
                'code'        => 'GAR-SBY-101',
                'size'        => 'M',
                'category'    => $catCoverall,
                'cycles'      => 9,
                'limit'       => 50,
                'stage'       => 'siap_digunakan',
                'tag_uid'     => 'E280555566667701',
                'status'      => 'active',
            ],
            [
                'code'        => 'GAR-SBY-102',
                'size'        => 'L',
                'category'    => $catCoverall,
                'cycles'      => 48, // Kritis (96%)
                'limit'       => 50,
                'stage'       => 'pre_autoclave',
                'tag_uid'     => 'E280555566667702',
                'status'      => 'active',
            ],
            [
                'code'        => 'GAR-SBY-103',
                'size'        => 'L',
                'category'    => $catBedah,
                'cycles'      => 36, // Warning (90%)
                'limit'       => 40,
                'stage'       => 'sedang_dipakai',
                'tag_uid'     => 'E280555566667703',
                'status'      => 'active',
            ],
            [
                'code'        => 'GAR-SBY-104',
                'size'        => 'S',
                'category'    => $catJasLab,
                'cycles'      => 18,
                'limit'       => 60,
                'stage'       => 'siap_digunakan',
                'tag_uid'     => 'E280555566667704',
                'status'      => 'active',
            ],
        ];

        // Proses input Garment & Tag Bandung
        $createdGarments = [];
        foreach ($itemsBdg as $item) {
            $tag = Tag::firstOrCreate(
                ['tag_uid' => $item['tag_uid']],
                [
                    'tag_type'            => 'UHF Laundry Tag Silikon',
                    'manufacturer'        => 'Fujitsu / Smartrac',
                    'division_id'         => $divBdg->division_id,
                    'rated_max_cycles'    => 200,
                    'total_cycles_used'   => $item['cycles'],
                    'status'              => 'active',
                    'date_first_deployed' => now()->subMonths(3),
                ]
            );

            $garment = Garment::firstOrCreate(
                ['garment_code' => $item['code']],
                [
                    'category_id'         => $item['category']->category_id,
                    'division_id'         => $divBdg->division_id,
                    'size'                => $item['size'],
                    'current_tag_id'      => $tag->tag_id,
                    'max_cycle_limit'     => $item['limit'],
                    'current_cycle_count' => $item['cycles'],
                    'status'              => $item['status'],
                    'current_stage'       => $item['stage'],
                    'stage_changed_at'    => now()->subHours(rand(1, 12)),
                    'date_first_used'     => now()->subMonths(3)->toDateString(),
                ]
            );

            TagGarmentBinding::firstOrCreate(
                ['tag_id' => $tag->tag_id, 'garment_id' => $garment->garment_id],
                [
                    'bound_at'   => now()->subMonths(3),
                    'bound_by'   => $superAdmin->user_id,
                    'is_current' => true,
                ]
            );

            $createdGarments[] = [
                'garment' => $garment,
                'tag'     => $tag,
                'device'  => $devBdgAutoclave,
                'gowning' => $devBdgGowning,
                'city'    => 'Bandung',
            ];
        }

        // Proses input Garment & Tag Surabaya
        foreach ($itemsSby as $item) {
            $tag = Tag::firstOrCreate(
                ['tag_uid' => $item['tag_uid']],
                [
                    'tag_type'            => 'UHF Laundry Tag Silikon',
                    'manufacturer'        => 'Fujitsu / Smartrac',
                    'division_id'         => $divSby->division_id,
                    'rated_max_cycles'    => 200,
                    'total_cycles_used'   => $item['cycles'],
                    'status'              => 'active',
                    'date_first_deployed' => now()->subMonths(2),
                ]
            );

            $garment = Garment::firstOrCreate(
                ['garment_code' => $item['code']],
                [
                    'category_id'         => $item['category']->category_id,
                    'division_id'         => $divSby->division_id,
                    'size'                => $item['size'],
                    'current_tag_id'      => $tag->tag_id,
                    'max_cycle_limit'     => $item['limit'],
                    'current_cycle_count' => $item['cycles'],
                    'status'              => $item['status'],
                    'current_stage'       => $item['stage'],
                    'stage_changed_at'    => now()->subHours(rand(1, 8)),
                    'date_first_used'     => now()->subMonths(2)->toDateString(),
                ]
            );

            TagGarmentBinding::firstOrCreate(
                ['tag_id' => $tag->tag_id, 'garment_id' => $garment->garment_id],
                [
                    'bound_at'   => now()->subMonths(2),
                    'bound_by'   => $superAdmin->user_id,
                    'is_current' => true,
                ]
            );

            $createdGarments[] = [
                'garment' => $garment,
                'tag'     => $tag,
                'device'  => $devSbyAutoclave,
                'gowning' => $devSbyGowning,
                'city'    => 'Surabaya',
            ];
        }

        // 7. HISTORI SCAN EVENTS (14 HARI TERAKHIR DARI BANDUNG & SURABAYA)
        foreach ($createdGarments as $gInfo) {
            $garment = $gInfo['garment'];
            $tag     = $gInfo['tag'];
            $devAuto = $gInfo['device'];
            $devGown = $gInfo['gowning'];

            for ($day = 14; $day >= 0; $day--) {
                if ($day % 2 === 0) {
                    $timeGown = Carbon::now()->subDays($day)->setTime(7, rand(30, 50));
                    $timePre  = Carbon::now()->subDays($day)->setTime(16, rand(10, 30));
                    $timePost = Carbon::now()->subDays($day)->setTime(17, rand(15, 45));

                    // Scan Gowning (usage_checkpoint)
                    ScanEvent::create([
                        'tag_id'            => $tag->tag_id,
                        'garment_id'        => $garment->garment_id,
                        'device_id'         => $devGown->device_id,
                        'event_type'        => 'usage_checkpoint',
                        'scan_timestamp'    => $timeGown,
                        'cycle_count_after' => max(0, $garment->current_cycle_count - intval($day / 2)),
                        'location'          => $devGown->location,
                    ]);

                    // Scan Pre-Autoclave
                    ScanEvent::create([
                        'tag_id'            => $tag->tag_id,
                        'garment_id'        => $garment->garment_id,
                        'device_id'         => $devAuto->device_id,
                        'event_type'        => 'pre_autoclave',
                        'scan_timestamp'    => $timePre,
                        'cycle_count_after' => max(0, $garment->current_cycle_count - intval($day / 2)),
                        'location'          => $devAuto->location,
                    ]);

                    // Scan Post-Autoclave (Cycle Complete)
                    ScanEvent::create([
                        'tag_id'            => $tag->tag_id,
                        'garment_id'        => $garment->garment_id,
                        'device_id'         => $devAuto->device_id,
                        'event_type'        => 'post_autoclave',
                        'scan_timestamp'    => $timePost,
                        'cycle_count_after' => max(1, $garment->current_cycle_count - intval($day / 2) + 1),
                        'location'          => $devAuto->location,
                    ]);
                }
            }
        }

        // 8. SAMPLE ALERTS MULTI-LOKASI
        // Alert 1: Kritis Bandung (GAR-BDG-002)
        $gBdg2 = Garment::where('garment_code', 'GAR-BDG-002')->first();
        if ($gBdg2) {
            Alert::create([
                'garment_id'   => $gBdg2->garment_id,
                'tag_id'       => $gBdg2->current_tag_id,
                'device_id'    => $devBdgAutoclave->device_id,
                'alert_type'   => 'cycle_warning',
                'severity'     => 'critical',
                'message'      => "[Pabrik Bandung] Garment {$gBdg2->garment_code} telah mencapai 46/50 siklus sterilisasi (92%). Segera jadwalkan baju pengganti.",
                'status'       => 'open',
                'triggered_at' => now()->subHours(3),
            ]);
        }

        // Alert 2: Kritis Surabaya (GAR-SBY-102)
        $gSby2 = Garment::where('garment_code', 'GAR-SBY-102')->first();
        if ($gSby2) {
            Alert::create([
                'garment_id'   => $gSby2->garment_id,
                'tag_id'       => $gSby2->current_tag_id,
                'device_id'    => $devSbyAutoclave->device_id,
                'alert_type'   => 'cycle_warning',
                'severity'     => 'critical',
                'message'      => "[Pabrik Surabaya] Garment {$gSby2->garment_code} telah mencapai 48/50 siklus sterilisasi (96%). Sisa 2 siklus sebelum pensiun.",
                'status'       => 'open',
                'triggered_at' => now()->subHours(1),
            ]);
        }

        // Alert 3: Warning Surabaya (GAR-SBY-103)
        $gSby3 = Garment::where('garment_code', 'GAR-SBY-103')->first();
        if ($gSby3) {
            Alert::create([
                'garment_id'   => $gSby3->garment_id,
                'tag_id'       => $gSby3->current_tag_id,
                'device_id'    => $devSbyGowning->device_id,
                'alert_type'   => 'cycle_warning',
                'severity'     => 'warning',
                'message'      => "[Pabrik Surabaya] Garment {$gSby3->garment_code} mendekati batas maksimal siklus (36/40 siklus, 90%).",
                'status'       => 'open',
                'triggered_at' => now()->subHours(5),
            ]);
        }

        // Alert 4: Acknowledged
        $gBdg4 = Garment::where('garment_code', 'GAR-BDG-004')->first();
        if ($gBdg4) {
            Alert::create([
                'garment_id'   => $gBdg4->garment_id,
                'tag_id'       => $gBdg4->current_tag_id,
                'device_id'    => $devBdgAutoclave->device_id,
                'alert_type'   => 'cycle_warning',
                'severity'     => 'warning',
                'message'      => "[Pabrik Bandung] Baju Bedah {$gBdg4->garment_code} mencapai 37/40 siklus.",
                'status'       => 'acknowledged',
                'triggered_at' => now()->subDays(1),
            ]);
        }

        // Alert 5: Resolved
        Alert::create([
            'garment_id'   => $gBdg2?->garment_id,
            'tag_id'       => $gBdg2?->current_tag_id,
            'device_id'    => $devBdgAutoclave->device_id,
            'alert_type'   => 'cycle_warning',
            'severity'     => 'warning',
            'message'      => "[Pabrik Bandung] Garment {$gBdg2?->garment_code} inspeksi visual lolos validasi QA.",
            'status'       => 'resolved',
            'triggered_at' => now()->subDays(3),
            'resolved_at'  => now()->subDays(2),
            'resolved_by'  => $qaSupervisor->user_id,
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
