<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tag_garment_bindings', function (Blueprint $table) {
            $table->uuid('binding_id')->primary();
            $table->uuid('tag_id');
            $table->uuid('garment_id');
            $table->timestamp('bound_at', 6)->useCurrent();
            $table->timestamp('unbound_at', 6)->nullable();
            $table->uuid('bound_by')->nullable();
            $table->string('unbind_reason', 100)->nullable();
            $table->boolean('is_current')->default(true);

            // Generated (STORED) columns, mirror the raw SQL exactly so
            // the "only one current binding per tag/garment" rule can be
            // enforced via unique index below.
            $table->char('active_tag_uid', 36)
                ->storedAs('IF(is_current = 1, tag_id, NULL)')
                ->nullable();
            $table->char('active_garment_uid', 36)
                ->storedAs('IF(is_current = 1, garment_id, NULL)')
                ->nullable();

            $table->foreign('tag_id', 'fk_tgb_tag')->references('tag_id')->on('tags');
            $table->foreign('garment_id', 'fk_tgb_garment')->references('garment_id')->on('garments');
            $table->foreign('bound_by', 'fk_tgb_bound_by')->references('user_id')->on('users');
        });

        DB::statement("ALTER TABLE tag_garment_bindings MODIFY binding_id CHAR(36) NOT NULL DEFAULT (UUID())");

        Schema::table('tag_garment_bindings', function (Blueprint $table) {
            $table->unique('active_tag_uid', 'uq_one_current_binding_per_tag');
            $table->unique('active_garment_uid', 'uq_one_current_binding_per_garment');
            $table->index('garment_id', 'idx_bindings_garment');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tag_garment_bindings');
    }
};
