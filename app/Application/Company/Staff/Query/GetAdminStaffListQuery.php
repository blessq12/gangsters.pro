<?php

namespace App\Application\Company\Staff\Query;

use App\Application\Company\Contracts\AdminUserRepository;

final class GetAdminStaffListQuery
{
    public function __construct(
        private readonly AdminUserRepository $users,
    ) {
    }

    /**
     * @return array{items: array<int, array<string, mixed>>, total: int}
     */
    public function execute(?string $search, int $page, int $perPage): array
    {
        return $this->users->list($search, $page, $perPage);
    }
}
