<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('garment_categories', function (Blueprint $table) {
            $table->uuid('category_id')->primary();
            $table->string('category_name', 100)->unique();
            $table->text('description')->nullable();
            $table->integer('default_max_cycle')->default(40);
            $table->timestamp('created_at', 6)->useCurrent();
        });

        DB::statement("ALTER TABLE garment_categories MODIFY category_id CHAR(36) NOT NULL DEFAULT (UUID())");
    }

    public function down(): void
    {
        Schema::dropIfExists('garment_categories');
    }
};
