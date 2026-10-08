<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS trg_update_cycle_counts");

        // NOTE: no DELIMITER directive needed — that is a mysql-client-only
        // construct. PDO sends the whole CREATE TRIGGER body (including the
        // internal semicolons) as a single statement via DB::unprepared().
        DB::unprepared("
            CREATE TRIGGER trg_update_cycle_counts
            AFTER INSERT ON scan_events
            FOR EACH ROW
            BEGIN
                IF NEW.event_type IN ('pre_autoclave','post_autoclave') THEN
                    UPDATE garments
                    SET current_cycle_count = current_cycle_count + 1,
                        updated_at = CURRENT_TIMESTAMP(6)
                    WHERE garment_id = NEW.garment_id;

                    UPDATE tags
                    SET total_cycles_used = total_cycles_used + 1,
                        updated_at = CURRENT_TIMESTAMP(6)
                    WHERE tag_id = NEW.tag_id;
                END IF;
            END
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS trg_update_cycle_counts");
    }
};
