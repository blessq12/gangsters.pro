<?php

namespace App\Application\Company\Staff\Command;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Company\Contracts\AdminUserRepository;

final class UpdateAdminUserUseCase
{
    public function __construct(
        private readonly AdminUserRepository $users,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function execute(int $id, array $data, ?int $actingUserId = null): array
    {
        if ($actingUserId !== null && $id === $actingUserId && array_key_exists('admin_role', $data)) {
            throw new ApiException('Нельзя изменить свою роль.', 422);
        }

        return $this->users->update($id, $data);
    }
}
