<?php

namespace App\Application\Company\Staff\Command;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Company\Contracts\AdminUserRepository;

final class DeleteAdminUserUseCase
{
    public function __construct(
        private readonly AdminUserRepository $users,
    ) {
    }

    public function execute(int $id, int $actingUserId): void
    {
        if ($id === $actingUserId) {
            throw new ApiException('Нельзя удалить свою учётную запись.', 422);
        }

        $this->users->delete($id);
    }
}
