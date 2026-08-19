<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('notification_id')->primary();
            $table->string('category', 50);
            $table->text('message');
            $table->timestamp('created_at', 6)->useCurrent();
        });

        DB::statement("ALTER TABLE notifications MODIFY notification_id CHAR(36) NOT NULL DEFAULT (UUID())");
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
