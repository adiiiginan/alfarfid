<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared("
            CREATE OR REPLACE VIEW vw_garment_retirement_forecast AS
            WITH recent_activity AS (
                SELECT
                    garment_id,
                    COUNT(*) AS cycles_last_14_days,
                    COUNT(DISTINCT DATE(scan_timestamp)) AS active_days
                FROM scan_events
                WHERE scan_timestamp >= NOW() - INTERVAL 14 DAY
                  AND event_type IN ('pre_autoclave','post_autoclave')
                GROUP BY garment_id
            )
            SELECT
                g.garment_id,
                g.garment_code,
                g.current_cycle_count,
                g.max_cycle_limit,
                (g.max_cycle_limit - g.current_cycle_count) AS remaining_cycles,
                COALESCE(
                    ra.cycles_last_14_days / NULLIF(14, 0),
                    gc.default_max_cycle / 40
                ) AS avg_cycles_per_day,
                CASE
                    WHEN COALESCE(ra.cycles_last_14_days, 0) = 0 THEN NULL
                    ELSE ROUND(
                        (g.max_cycle_limit - g.current_cycle_count)
                        / (ra.cycles_last_14_days / 14), 1
                    )
                END AS estimated_days_remaining
            FROM garments g
            LEFT JOIN recent_activity ra ON ra.garment_id = g.garment_id
            LEFT JOIN garment_categories gc ON gc.category_id = g.category_id
            WHERE g.status = 'active'
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP VIEW IF EXISTS vw_garment_retirement_forecast");
    }
};
