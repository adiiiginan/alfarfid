<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('autoclave_machines', function (Blueprint $table) {
            $table->uuid('machine_id')->primary();
            $table->string('machine_name', 100);
            $table->string('location', 150)->nullable();
            $table->date('install_date')->nullable();
            $table->string('status', 20)->default('active');
        });

        DB::statement("ALTER TABLE autoclave_machines MODIFY machine_id CHAR(36) NOT NULL DEFAULT (UUID())");

        DB::statement("ALTER TABLE autoclave_machines ADD CONSTRAINT chk_machine_status CHECK (
            status IN ('active','maintenance','decommissioned')
        )");
    }

    public function down(): void
    {
        Schema::dropIfExists('autoclave_machines');
    }
};
