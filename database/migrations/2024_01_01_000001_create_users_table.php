<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('user_id')->primary();
            $table->string('full_name', 150);
            $table->string('email', 150)->unique();
            $table->string('password_hash', 255);
            $table->string('role', 30);
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at', 6)->useCurrent();
            $table->timestamp('updated_at', 6)->useCurrent()->useCurrentOnUpdate();
        });

        DB::statement("ALTER TABLE users MODIFY user_id CHAR(36) NOT NULL DEFAULT (UUID())");

        DB::statement("ALTER TABLE users ADD CONSTRAINT chk_users_role CHECK (
            role IN ('admin','operator','qa_supervisor','management','it_admin','auditor')
        )");
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
