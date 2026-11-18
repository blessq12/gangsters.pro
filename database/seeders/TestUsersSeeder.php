<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Тестовые сотрудники BO (4 роли + inactive). Только non-production.
 */
class TestUsersSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $users = [
            [
                'email' => 'owner@gangsters.test',
                'name' => 'Test Owner',
                'role' => 'owner',
                'is_active' => true,
            ],
            [
                'email' => 'admin@gangsters.test',
                'name' => 'Test Admin',
                'role' => 'administrator',
                'is_active' => true,
            ],
            [
                'email' => 'kitchen@gangsters.test',
                'name' => 'Test Kitchen',
                'role' => 'kitchen',
                'is_active' => true,
            ],
            [
                'email' => 'courier@gangsters.test',
                'name' => 'Test Courier',
                'role' => 'courier',
                'is_active' => true,
            ],
            [
                'email' => 'inactive@gangsters.test',
                'name' => 'Test Inactive',
                'role' => 'administrator',
                'is_active' => false,
            ],
        ];

        foreach ($users as $user) {
            User::query()->updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'role' => $user['role'],
                    'is_active' => $user['is_active'],
                    'password' => 'password',
                    'email_verified_at' => now(),
                ],
            );
        }
    }
}
