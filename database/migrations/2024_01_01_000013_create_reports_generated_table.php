<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports_generated', function (Blueprint $table) {
            $table->uuid('report_id')->primary();
            $table->string('report_type', 50);
            $table->uuid('generated_by')->nullable();
            $table->date('date_range_start')->nullable();
            $table->date('date_range_end')->nullable();
            $table->string('file_path', 255)->nullable();
            $table->timestamp('generated_at', 6)->useCurrent();

            $table->foreign('generated_by', 'fk_reports_generated_by')->references('user_id')->on('users');
        });

        DB::statement("ALTER TABLE reports_generated MODIFY report_id CHAR(36) NOT NULL DEFAULT (UUID())");
    }

    public function down(): void
    {
        Schema::dropIfExists('reports_generated');
    }
};
