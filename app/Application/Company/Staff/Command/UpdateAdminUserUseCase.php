<?php

namespace App\Application\Company\Staff\Command;

use App\Application\Company\Contracts\AdminUserRepository;

final class UpdateAdminUserUseCase
{
    public function __construct(
        private readonly AdminUserRepository $users,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function execute(int $id, array $data): array
    {
        return $this->users->update($id, $data);
    }
}
