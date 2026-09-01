<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->firstOrNew(['email' => 'admin@tabaraktrading.co']);
        $admin->forceFill([
            'name' => 'Tabarak Administrator',
            'password' => 'password',
            'is_admin' => true,
            'email_verified_at' => now(),
        ])->save();
    }
}
