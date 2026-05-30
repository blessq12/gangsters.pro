<?php

namespace App\Application\Company\Staff\Command;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Company\Contracts\AdminUserRepository;

final class CreateAdminUserUseCase
{
    public function __construct(
        private readonly AdminUserRepository $users,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function execute(array $data): array
    {
        if (blank($data['email'] ?? null) || blank($data['password'] ?? null)) {
            throw new ApiException('Email и пароль обязательны.', 422);
        }

        return $this->users->create($data);
    }
}
