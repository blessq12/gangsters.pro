<?php

namespace App\Application\Company\Query;

use App\Application\Company\Presenter\CompanyPresenter;
use App\Domain\Supporting\Company\Repository\BranchRepository;

final class GetAdminBranchUseCase
{
    public function __construct(
        private readonly BranchRepository $branches,
        private readonly CompanyPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(int $id): array
    {
        $branch = $this->branches->findById($id);
        if ($branch === null) {
            throw new \InvalidArgumentException('Филиал не найден.');
        }

        return $this->presenter->branch($branch);
    }
}
