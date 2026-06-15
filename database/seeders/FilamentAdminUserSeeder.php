<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class FilamentAdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'root@root.com'],
            [
                'name' => 'Root',
                'password' => 'password',
                'email_verified_at' => now(),
            ],
        );
    }
}
