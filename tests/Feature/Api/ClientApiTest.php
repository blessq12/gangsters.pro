<?php

namespace Tests\Feature\Api;

use Illuminate\Support\Facades\DB;

final class ClientApiTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->skipUnlessTablesExist(['UR_clients', 'personal_access_tokens']);
    }

    public function test_register_returns_client_and_token_matching_presenter_contract(): void
    {
        $phone = $this->uniquePhone();
        $response = $this->postJson('/api/client/register', $this->registerPayload($phone));

        $response->assertOk();
        $data = $response->json();
        $this->assertArrayHasKey('client', $data);
        $this->assertArrayHasKey('token', $data);
        $this->assertClientPresenterContract($data['client']);
    }

    public function test_register_validation_422_when_name_missing(): void
    {
        $payload = $this->registerPayload($this->uniquePhone());
        unset($payload['name']);

        $this->postJson('/api/client/register', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_register_validation_422_when_consent_missing(): void
    {
        $payload = $this->registerPayload($this->uniquePhone());
        unset($payload['consent_personal_data']);

        $this->postJson('/api/client/register', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['consent_personal_data']);
    }

    public function test_register_duplicate_phone_returns_422(): void
    {
        $phone = $this->uniquePhone();
        $this->postJson('/api/client/register', $this->registerPayload($phone))->assertOk();

        $this->postJson('/api/client/register', $this->registerPayload($phone))
            ->assertStatus(422)
            ->assertJsonPath('message', 'Client with this phone already exists');
    }

    public function test_login_returns_token_and_client_contract(): void
    {
        $session = $this->registerClientViaApi('my-password-99');

        $response = $this->postJson('/api/client/login', [
            'phone' => $session['phone'],
            'password' => $session['password'],
        ]);

        $response->assertOk();
        $data = $response->json();
        $this->assertArrayHasKey('token', $data);
        $this->assertArrayHasKey('client', $data);
        $this->assertClientPresenterContract($data['client']);
    }

    public function test_login_validation_422_without_password(): void
    {
        $this->postJson('/api/client/login', ['phone' => $this->uniquePhone()])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_login_422_when_phone_and_email_missing(): void
    {
        $this->postJson('/api/client/login', [
            'password' => 'x',
        ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Phone or email is required');
    }

    public function test_login_422_invalid_credentials(): void
    {
        $session = $this->registerClientViaApi('right-pass');

        $this->postJson('/api/client/login', [
            'phone' => $session['phone'],
            'password' => 'wrong-pass',
        ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Invalid credentials');
    }

    public function test_profile_401_without_token(): void
    {
        $this->getJson('/api/client/profile')->assertUnauthorized();
    }

    public function test_profile_200_contract(): void
    {
        $session = $this->registerClientViaApi();
        $response = $this->getJson('/api/client/profile', $this->bearerSanctum($session['token']));

        $response->assertOk();
        $data = $response->json();
        $this->assertArrayHasKey('client', $data);
        $this->assertClientPresenterContract($data['client']);
    }

    public function test_update_profile_401_without_token(): void
    {
        $this->patchJson('/api/client/profile', ['name' => 'X'])->assertUnauthorized();
    }

    public function test_update_profile_200(): void
    {
        $session = $this->registerClientViaApi();
        $response = $this->patchJson(
            '/api/client/profile',
            ['name' => 'Updated Api Name'],
            $this->bearerSanctum($session['token']),
        );

        $response->assertOk();
        $data = $response->json();
        $this->assertArrayHasKey('client', $data);
        $this->assertSame('Updated Api Name', $data['client']['name']);
        $this->assertClientPresenterContract($data['client']);
    }

    public function test_update_profile_validation_422_invalid_email(): void
    {
        $session = $this->registerClientViaApi();

        $this->patchJson(
            '/api/client/profile',
            ['email' => 'not-an-email'],
            $this->bearerSanctum($session['token']),
        )
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_forgot_password_200_returns_generic_message(): void
    {
        $email = $this->uniqueEmail();
        $this->registerClientViaApi('secret12', ['email' => $email]);

        $response = $this->postJson('/api/client/forgot-password', ['email' => $email]);

        $response->assertOk();
        $response->assertJsonPath('status', true);
        $response->assertJsonPath('message', 'Password reset instructions sent');
    }

    public function test_forgot_password_validation_422(): void
    {
        $this->postJson('/api/client/forgot-password', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_forgot_password_200_unknown_email(): void
    {
        $this->postJson('/api/client/forgot-password', ['email' => $this->uniqueEmail()])
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'Password reset instructions sent');
    }

    public function test_change_password_200_and_client_contract(): void
    {
        $email = $this->uniqueEmail();
        $this->registerClientViaApi('old-pass-11', ['email' => $email]);

        $this->postJson('/api/client/forgot-password', ['email' => $email])->assertOk();
        $token = DB::table('UR_clients')
            ->where('email', $email)
            ->value('password_reset_token');
        $this->assertNotEmpty($token);

        $response = $this->postJson('/api/client/change-password', [
            'token' => $token,
            'password' => 'new-pass-22',
        ]);

        $response->assertOk();
        $response->assertJsonPath('status', true);
        $data = $response->json();
        $this->assertArrayHasKey('client', $data);
        $this->assertClientPresenterContract($data['client']);
    }

    public function test_change_password_422_invalid_token(): void
    {
        $this->postJson('/api/client/change-password', [
            'token' => 'deadbeef-not-a-token',
            'password' => 'newsecret12',
        ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Invalid token');
    }

    public function test_change_password_422_when_token_expired(): void
    {
        $email = $this->uniqueEmail();
        $this->registerClientViaApi('old-pass-11', ['email' => $email]);
        $this->postJson('/api/client/forgot-password', ['email' => $email])->assertOk();

        $token = DB::table('UR_clients')->where('email', $email)->value('password_reset_token');
        $this->assertNotEmpty($token);

        DB::table('UR_clients')
            ->where('email', $email)
            ->update(['password_reset_requested_at' => now()->subHours(2)]);

        $this->postJson('/api/client/change-password', [
            'token' => $token,
            'password' => 'newsecret12',
        ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Invalid token');
    }

    public function test_add_address_401_without_token(): void
    {
        $this->postJson('/api/client/addresses', [
            'street' => 'Ленина',
            'house' => '1',
        ])->assertUnauthorized();
    }

    public function test_add_address_200_contract(): void
    {
        $session = $this->registerClientViaApi();

        $response = $this->postJson(
            '/api/client/addresses',
            [
                'street' => 'Тестовая',
                'house' => '42',
                'entrance' => '2',
                'apartment' => '10',
            ],
            $this->bearerSanctum($session['token']),
        );

        $response->assertOk();
        $data = $response->json();
        $this->assertArrayHasKey('client', $data);
        $this->assertClientPresenterContract($data['client']);
        $this->assertNotEmpty($data['client']['addresses']);
    }

    public function test_add_address_validation_422_without_street(): void
    {
        $session = $this->registerClientViaApi();

        $this->postJson(
            '/api/client/addresses',
            ['house' => '1'],
            $this->bearerSanctum($session['token']),
        )
            ->assertStatus(422)
            ->assertJsonValidationErrors(['street']);
    }

    public function test_delete_address_401_without_token(): void
    {
        $this->deleteJson('/api/client/addresses/1')->assertUnauthorized();
    }

    public function test_delete_address_200(): void
    {
        $session = $this->registerClientViaApi();

        $this->postJson(
            '/api/client/addresses',
            ['street' => 'Улица', 'house' => '5'],
            $this->bearerSanctum($session['token']),
        )->assertOk();

        $client = $this->getJson('/api/client/profile', $this->bearerSanctum($session['token']))
            ->assertOk()
            ->json('client');

        $this->assertNotEmpty($client['addresses']);
        $addressId = $client['addresses'][0]['id'];

        $response = $this->deleteJson(
            '/api/client/addresses/'.$addressId,
            [],
            $this->bearerSanctum($session['token']),
        );

        $response->assertOk();
        $this->assertArrayHasKey('client', $response->json());
        $this->assertClientPresenterContract($response->json('client'));
    }
}
