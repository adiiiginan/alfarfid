<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scan_events', function (Blueprint $table) {
            $table->uuid('event_id')->primary();
            $table->uuid('tag_id');
            $table->uuid('garment_id');
            $table->uuid('session_id')->nullable();
            $table->uuid('device_id');
            $table->string('event_type', 30);
            $table->timestamp('scan_timestamp', 6)->useCurrent();
            $table->integer('cycle_count_after');
            $table->string('location', 150)->nullable();
            $table->timestamp('created_at', 6)->useCurrent();

            $table->foreign('tag_id', 'fk_scan_tag')->references('tag_id')->on('tags');
            $table->foreign('garment_id', 'fk_scan_garment')->references('garment_id')->on('garments');
            $table->foreign('session_id', 'fk_scan_session')->references('session_id')->on('autoclave_sessions');
            $table->foreign('device_id', 'fk_scan_device')->references('device_id')->on('devices');
        });

        DB::statement("ALTER TABLE scan_events MODIFY event_id CHAR(36) NOT NULL DEFAULT (UUID())");

        DB::statement("ALTER TABLE scan_events ADD CONSTRAINT chk_event_type CHECK (
            event_type IN ('pre_autoclave','post_autoclave','usage_checkpoint','manual_reentry')
        )");

        // DESC composite indexes match the original schema's query pattern
        // (most-recent-scan-first lookups), which Schema::index() can't
        // express directly, so these are raw.
        DB::statement("CREATE INDEX idx_scan_events_tag_time ON scan_events(tag_id, scan_timestamp DESC)");
        DB::statement("CREATE INDEX idx_scan_events_garment_time ON scan_events(garment_id, scan_timestamp DESC)");
        DB::statement("CREATE INDEX idx_scan_events_device_time ON scan_events(device_id, scan_timestamp DESC)");

        Schema::table('scan_events', function (Blueprint $table) {
            $table->index('session_id', 'idx_scan_events_session');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scan_events');
    }
};
