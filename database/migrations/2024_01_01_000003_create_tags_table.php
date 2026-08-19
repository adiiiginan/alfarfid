<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->uuid('tag_id')->primary();
            $table->string('tag_uid', 64)->unique();
            $table->string('tag_type', 50)->nullable();
            $table->string('manufacturer', 100)->nullable();
            $table->integer('rated_max_cycles')->default(200);
            $table->integer('total_cycles_used')->default(0);
            $table->string('status', 20)->default('active');
            $table->date('date_first_deployed')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at', 6)->useCurrent();
            $table->timestamp('updated_at', 6)->useCurrent()->useCurrentOnUpdate();
        });

        DB::statement("ALTER TABLE tags MODIFY tag_id CHAR(36) NOT NULL DEFAULT (UUID())");

        DB::statement("ALTER TABLE tags ADD CONSTRAINT chk_tags_status CHECK (
            status IN ('active','retired','damaged','lost')
        )");
    }

    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};
