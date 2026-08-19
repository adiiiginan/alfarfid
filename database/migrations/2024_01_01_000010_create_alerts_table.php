<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->uuid('alert_id')->primary();
            $table->uuid('garment_id')->nullable();
            $table->uuid('tag_id')->nullable();
            $table->uuid('device_id')->nullable();
            $table->string('alert_type', 30);
            $table->string('severity', 20)->default('warning');
            $table->text('message');
            $table->timestamp('triggered_at', 6)->useCurrent();
            $table->timestamp('resolved_at', 6)->nullable();
            $table->uuid('resolved_by')->nullable();
            $table->string('status', 20)->default('open');

            $table->foreign('garment_id', 'fk_alerts_garment')->references('garment_id')->on('garments');
            $table->foreign('tag_id', 'fk_alerts_tag')->references('tag_id')->on('tags');
            $table->foreign('device_id', 'fk_alerts_device')->references('device_id')->on('devices');
            $table->foreign('resolved_by', 'fk_alerts_resolved_by')->references('user_id')->on('users');
        });

        DB::statement("ALTER TABLE alerts MODIFY alert_id CHAR(36) NOT NULL DEFAULT (UUID())");

        DB::statement("ALTER TABLE alerts ADD CONSTRAINT chk_alert_type CHECK (
            alert_type IN ('cycle_warning','cycle_exceeded','device_offline','tag_wear_warning')
        )");
        DB::statement("ALTER TABLE alerts ADD CONSTRAINT chk_alert_severity CHECK (
            severity IN ('info','warning','critical')
        )");
        DB::statement("ALTER TABLE alerts ADD CONSTRAINT chk_alert_status CHECK (
            status IN ('open','acknowledged','resolved')
        )");

        Schema::table('alerts', function (Blueprint $table) {
            $table->index('status', 'idx_alerts_status');
            $table->index('garment_id', 'idx_alerts_garment');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
