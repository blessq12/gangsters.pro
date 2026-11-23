<?php

namespace Database\Seeders;

use App\Infrastructure\Staff\Model\EmployeeModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Тестовые сотрудники BO (4 роли + inactive). Только non-production.
 */
class TestEmployeesSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $employees = [
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

        foreach ($employees as $employee) {
            EmployeeModel::query()->updateOrCreate(
                ['email' => $employee['email']],
                [
                    'name' => $employee['name'],
                    'role' => $employee['role'],
                    'is_active' => $employee['is_active'],
                    'phone' => null,
                    'password_hash' => Hash::make('password'),
                ],
            );
        }
    }
}
