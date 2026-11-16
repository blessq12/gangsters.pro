<?php

namespace Tests\Feature\Backoffice;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\ApiTestCase;

final class BackofficeAuthTest extends ApiTestCase
{
    /** @var list<string> */
    protected array $requiredTables = ['users', 'personal_access_tokens'];

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasColumn('users', 'role') || ! Schema::hasColumn('users', 'is_active')) {
            $this->markTestSkipped('users.role/is_active missing — run migrations.');
        }

        config([
            'backoffice.enabled' => true,
            'backoffice.auth_required' => true,
        ]);
    }

    public function test_login_logout_and_me(): void
    {
        $user = User::query()->create([
            'name' => 'Auth Tester',
            'email' => 'auth-tester-'.bin2hex(random_bytes(4)).'@gangsta.local',
            'password' => Hash::make('secret12'),
            'role' => 'administrator',
            'is_active' => true,
        ]);

        $login = $this->postJson('/api/backoffice/auth/login', [
            'email' => $user->email,
            'password' => 'secret12',
        ]);

        $login->assertOk()
            ->assertJsonStructure(['token', 'employee'])
            ->assertJsonPath('employee.id', $user->id)
            ->assertJsonPath('employee.role', 'administrator');

        $token = (string) $login->json('token');
        $this->assertNotSame('', $token);

        $this->withBearer($token)
            ->getJson('/api/backoffice/auth/me')
            ->assertOk()
            ->assertJsonPath('employee.id', $user->id)
            ->assertJsonPath('auth_required', true);

        $this->withBearer($token)
            ->postJson('/api/backoffice/auth/logout')
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->withBearer($token)
            ->getJson('/api/backoffice/auth/me')
            ->assertOk()
            ->assertJsonPath('employee', null);

        $this->withBearer($token)
            ->getJson('/api/backoffice/ping')
            ->assertUnauthorized();
    }

    public function test_protected_route_requires_auth_when_flag_on(): void
    {
        $this->getJson('/api/backoffice/ping')
            ->assertUnauthorized();
    }

    public function test_protected_route_open_when_auth_flag_off(): void
    {
        config(['backoffice.auth_required' => false]);

        $this->getJson('/api/backoffice/ping')
            ->assertOk()
            ->assertJsonPath('ok', true);
    }

    public function test_forgot_password_stub(): void
    {
        $this->postJson('/api/backoffice/auth/forgot-password', [
            'email' => 'anyone@gangsta.local',
        ])
            ->assertOk()
            ->assertJsonStructure(['message']);
    }
}
