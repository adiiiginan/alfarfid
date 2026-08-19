<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('autoclave_sessions', function (Blueprint $table) {
            $table->uuid('session_id')->primary();
            $table->uuid('machine_id');
            $table->date('session_date');
            $table->smallInteger('session_number');
            $table->timestamp('start_time', 6)->nullable();
            $table->timestamp('end_time', 6)->nullable();
            $table->uuid('operator_id')->nullable();
            $table->string('status', 20)->default('scheduled');

            $table->foreign('machine_id', 'fk_sessions_machine')
                ->references('machine_id')->on('autoclave_machines');
            $table->foreign('operator_id', 'fk_sessions_operator')
                ->references('user_id')->on('users');

            $table->unique(['machine_id', 'session_date', 'session_number'], 'uq_session');
        });

        DB::statement("ALTER TABLE autoclave_sessions MODIFY session_id CHAR(36) NOT NULL DEFAULT (UUID())");

        DB::statement("ALTER TABLE autoclave_sessions ADD CONSTRAINT chk_session_status CHECK (
            status IN ('scheduled','running','completed','cancelled')
        )");

        Schema::table('autoclave_sessions', function (Blueprint $table) {
            $table->index('session_date', 'idx_sessions_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('autoclave_sessions');
    }
};
