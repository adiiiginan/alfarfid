<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('garments', function (Blueprint $table) {
            $table->uuid('garment_id')->primary();
            $table->string('garment_code', 50)->unique();
            $table->uuid('category_id');
            $table->string('size', 20)->nullable();
            $table->uuid('current_tag_id')->nullable();
            $table->integer('max_cycle_limit');
            $table->integer('current_cycle_count')->default(0);
            $table->string('status', 20)->default('active');
            $table->date('date_first_used')->nullable();
            $table->date('date_retired')->nullable();
            $table->timestamp('created_at', 6)->useCurrent();
            $table->timestamp('updated_at', 6)->useCurrent()->useCurrentOnUpdate();

            $table->foreign('category_id', 'fk_garments_category')
                ->references('category_id')->on('garment_categories');
            $table->foreign('current_tag_id', 'fk_garments_current_tag')
                ->references('tag_id')->on('tags');
        });

        DB::statement("ALTER TABLE garments MODIFY garment_id CHAR(36) NOT NULL DEFAULT (UUID())");

        DB::statement("ALTER TABLE garments ADD CONSTRAINT chk_garments_status CHECK (
            status IN ('active','retired','damaged','in_repair')
        )");

        Schema::table('garments', function (Blueprint $table) {
            $table->index('status', 'idx_garments_status');
            $table->index('current_tag_id', 'idx_garments_current_tag');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('garments');
    }
};
