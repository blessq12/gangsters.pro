<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class StaffAdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'root@root.com'],
            [
                'name' => 'Root',
                'password' => 'password',
                'role' => 'owner',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );
    }
}
