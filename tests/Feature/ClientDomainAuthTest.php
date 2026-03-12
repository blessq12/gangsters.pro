<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ClientDomainAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_returns_client_and_token(): void
    {
        $response = $this->postJson('/api/test-client/register', [
            'name' => 'John Doe',
            'phone' => '+7 (999) 888-77-66',
            'email' => 'john@example.com',
            'birth_date' => '1990-01-01',
            'password' => 'secret123',
            'consent_personal_data' => true,
            'consent_marketing' => false,
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'client' => [
                    'id',
                    'name',
                    'phone',
                    'email',
                    'birth_date',
                    'status',
                    'consent_personal_data',
                    'consent_marketing',
                    'default_address_id',
                    'addresses',
                    'created_at',
                    'updated_at',
                ],
                'token',
            ]);
    }

    public function test_login_returns_client_and_token(): void
    {
        $this->postJson('/api/test-client/register', [
            'name' => 'Jane Doe',
            'phone' => '+7 (999) 000-11-22',
            'email' => 'jane@example.com',
            'birth_date' => '1992-02-02',
            'password' => 'secret123',
            'consent_personal_data' => true,
            'consent_marketing' => false,
        ])->assertStatus(200);

        $response = $this->postJson('/api/test-client/login', [
            'email' => 'jane@example.com',
            'password' => 'secret123',
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'client' => [
                    'id',
                    'name',
                    'phone',
                    'email',
                    'birth_date',
                    'status',
                    'consent_personal_data',
                    'consent_marketing',
                    'default_address_id',
                    'addresses',
                    'created_at',
                    'updated_at',
                ],
                'token',
            ]);
    }

    public function test_me_returns_client_with_token_in_header(): void
    {
        $registerResponse = $this->postJson('/api/test-client/register', [
            'name' => 'John Doe',
            'phone' => '+7 (999) 888-77-66',
            'email' => 'john@example.com',
            'birth_date' => '1990-01-01',
            'password' => 'secret123',
            'consent_personal_data' => true,
            'consent_marketing' => false,
        ])->assertStatus(200);

        $token = $registerResponse->json('token');

        $meResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/test-client/me');

        $meResponse
            ->assertStatus(200)
            ->assertJsonStructure([
                'client' => [
                    'id',
                    'name',
                    'phone',
                    'email',
                    'birth_date',
                    'status',
                    'consent_personal_data',
                    'consent_marketing',
                    'default_address_id',
                    'addresses',
                    'created_at',
                    'updated_at',
                ],
            ]);
    }
}

