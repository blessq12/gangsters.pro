<?php

namespace Tests\Feature\Admin;

use App\Domain\Admin\Enums\AdminRole;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class AdminRoleAccessTest extends TestCase
{
    /**
     * @return array<string, array{0: AdminRole, 1: list<string>, 2: list<string>}>
     */
    public static function roleHubMatrixProvider(): array
    {
        return [
            'super_admin' => [
                AdminRole::SuperAdmin,
                ['/admin/dashboard', '/admin/operations', '/admin/catalog', '/admin/marketing', '/admin/company'],
                [],
            ],
            'operations' => [
                AdminRole::Operations,
                ['/admin/dashboard', '/admin/operations'],
                ['/admin/catalog', '/admin/marketing', '/admin/company'],
            ],
            'catalog' => [
                AdminRole::Catalog,
                ['/admin/catalog', '/admin/marketing'],
                ['/admin/dashboard', '/admin/operations', '/admin/company'],
            ],
            'company' => [
                AdminRole::Company,
                ['/admin/company'],
                ['/admin/dashboard', '/admin/operations', '/admin/catalog', '/admin/marketing'],
            ],
            'read_only' => [
                AdminRole::ReadOnly,
                ['/admin/dashboard', '/admin/operations', '/admin/catalog', '/admin/marketing', '/admin/company'],
                [],
            ],
        ];
    }

    #[DataProvider('roleHubMatrixProvider')]
    public function test_role_hub_access_matrix(AdminRole $role, array $allowedPaths, array $deniedPaths): void
    {
        $this->skipUnlessUsersTableExists();

        $user = User::factory()->staff($role)->create();

        foreach ($allowedPaths as $path) {
            $this->actingAs($user)
                ->get($path)
                ->assertOk();
        }

        foreach ($deniedPaths as $path) {
            $this->actingAs($user)
                ->get($path)
                ->assertForbidden();
        }
    }

    public function test_user_without_admin_role_cannot_access_panel(): void
    {
        $this->skipUnlessUsersTableExists();

        $user = User::factory()->create(['admin_role' => null]);

        $this->actingAs($user)
            ->get('/admin/dashboard')
            ->assertForbidden();
    }

    public function test_read_only_cannot_open_staff_create_page(): void
    {
        $this->skipUnlessUsersTableExists();

        $user = User::factory()->staff(AdminRole::ReadOnly)->create();

        $this->actingAs($user)
            ->get('/admin/company/staff/create')
            ->assertForbidden();
    }

    public function test_company_role_can_open_staff_create_page(): void
    {
        $this->skipUnlessUsersTableExists();

        $user = User::factory()->staff(AdminRole::Company)->create();

        $this->actingAs($user)
            ->get('/admin/company/staff/create')
            ->assertOk();
    }

    public function test_update_admin_user_use_case_blocks_self_role_change(): void
    {
        $this->skipUnlessUsersTableExists();

        $user = User::factory()->staff(AdminRole::SuperAdmin)->create();

        $this->expectException(\App\Application\Common\Exceptions\ApiException::class);
        $this->expectExceptionMessage('Нельзя изменить свою роль.');

        app(\App\Application\Company\Staff\Command\UpdateAdminUserUseCase::class)->execute(
            (int) $user->id,
            ['admin_role' => AdminRole::ReadOnly->value],
            (int) $user->id,
        );
    }

    private function skipUnlessUsersTableExists(): void
    {
        if (! Schema::hasTable('users')) {
            $this->markTestSkipped('Нет таблицы `users` — выполни миграции на выбранной для тестов БД.');
        }

        if (! Schema::hasColumn('users', 'admin_role')) {
            $this->markTestSkipped('Нет колонки `users.admin_role` — выполни миграции.');
        }
    }
}
