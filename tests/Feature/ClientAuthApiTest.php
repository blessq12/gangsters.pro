<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ClientAuthApiTest extends TestCase
{
    #[Test]
    public function register_и_login_возвращают_токен(): void
    {
        if (! $this->databaseIsAvailable()) {
            $this->markTestSkipped('БД недоступна для feature-теста.');
        }

        $phone = '+7912'.random_int(1000000, 9999999);
        $email = 'test_'.random_int(100000, 999999).'@example.com';

        $registerResponse = $this->postJson('/api/client/register', [
            'name' => 'Тест Клиент',
            'phone' => $phone,
            'email' => $email,
            'password' => 'secret123',
            'consent_personal_data' => true,
        ]);

        $registerResponse->assertCreated();
        $registerResponse->assertJsonStructure(['token', 'client' => ['id', 'name']]);

        $loginResponse = $this->postJson('/api/client/login', [
            'phone' => $phone,
            'password' => 'secret123',
        ]);

        $loginResponse->assertOk();
        $loginResponse->assertJsonStructure(['token', 'client']);
    }

    private function databaseIsAvailable(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
