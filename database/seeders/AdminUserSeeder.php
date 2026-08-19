<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'full_name' => 'Super Admin',
            'email' => 'admin@alfarfid.local',
            'password_hash' => Hash::make('admin'),
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);
    }
}
