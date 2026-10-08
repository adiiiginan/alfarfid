<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->uuid('device_id')->primary();
            $table->string('device_name', 100);
            $table->string('location', 150)->nullable();
            $table->string('mac_address', 20)->unique();
            $table->string('api_key_hash', 255);
            $table->string('status', 20)->default('offline');
            $table->timestamp('last_heartbeat_at', 6)->nullable();
            $table->date('installed_at')->nullable();
            $table->timestamp('created_at', 6)->useCurrent();
        });

        DB::statement("ALTER TABLE devices MODIFY device_id CHAR(36) NOT NULL DEFAULT (UUID())");

        DB::statement("ALTER TABLE devices ADD CONSTRAINT chk_device_status CHECK (
            status IN ('online','offline','maintenance')
        )");
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
