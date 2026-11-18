<?php

namespace Database\Seeders;

use App\Infrastructure\Crm\Model\CRM_Client;
use App\Shared\ValueObject\PhoneNumber;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Тестовый клиент витрины. Только non-production.
 */
class TestClientSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $phone = PhoneNumber::formatFromRaw('+79000000000');

        CRM_Client::query()->updateOrCreate(
            ['email' => 'client@gangsters.test'],
            [
                'name' => 'Тестовый Клиент',
                'phone' => $phone,
                'password' => Hash::make('password'),
                'consent_personal_data' => true,
                'consent_marketing' => false,
            ],
        );
    }
}
