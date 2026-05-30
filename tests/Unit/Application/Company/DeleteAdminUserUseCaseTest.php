<?php

namespace Tests\Unit\Application\Company;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Company\Contracts\AdminUserRepository;
use App\Application\Company\Staff\Command\DeleteAdminUserUseCase;
use Mockery;
use PHPUnit\Framework\TestCase;

final class DeleteAdminUserUseCaseTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_rejects_deleting_own_account(): void
    {
        $repository = Mockery::mock(AdminUserRepository::class);
        $repository->shouldNotReceive('delete');

        $useCase = new DeleteAdminUserUseCase($repository);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Нельзя удалить свою учётную запись.');

        $useCase->execute(5, 5);
    }

    public function test_deletes_other_user(): void
    {
        $repository = Mockery::mock(AdminUserRepository::class);
        $repository->shouldReceive('delete')->once()->with(7);

        $useCase = new DeleteAdminUserUseCase($repository);

        $useCase->execute(7, 5);

        $this->assertTrue(true);
    }
}
