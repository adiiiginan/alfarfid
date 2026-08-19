<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS trg_cegah_reset_counter");

        DB::unprepared("
            CREATE TRIGGER trg_cegah_reset_counter
            BEFORE UPDATE ON garments
            FOR EACH ROW
            BEGIN
                IF NEW.current_cycle_count <> OLD.current_cycle_count THEN
                    IF NEW.current_cycle_count = 0
                       AND OLD.current_cycle_count <> 0
                       AND NOT EXISTS (
                            SELECT 1 FROM tag_garment_bindings b
                            WHERE b.garment_id = NEW.garment_id
                              AND b.is_current = TRUE
                              AND b.bound_at >= NOW(6) - INTERVAL 5 SECOND
                       )
                    THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'Reset current_cycle_count ke 0 ditolak: tidak terdeteksi binding tag baru yang sah. Gunakan proses_tag_reassignment().';
                    END IF;
                END IF;
            END
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS trg_cegah_reset_counter");
    }
};
