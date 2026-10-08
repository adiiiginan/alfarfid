<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('log_id')->primary();
            $table->string('table_name', 50);
            $table->uuid('record_id');
            $table->string('action', 10);
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->uuid('changed_by')->nullable();
            $table->timestamp('changed_at', 6)->useCurrent();

            $table->foreign('changed_by', 'fk_audit_changed_by')->references('user_id')->on('users');
        });

        DB::statement("ALTER TABLE audit_logs MODIFY log_id CHAR(36) NOT NULL DEFAULT (UUID())");

        DB::statement("ALTER TABLE audit_logs ADD CONSTRAINT chk_audit_action CHECK (
            action IN ('insert','update','delete')
        )");

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index(['table_name', 'record_id'], 'idx_audit_table_record');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
