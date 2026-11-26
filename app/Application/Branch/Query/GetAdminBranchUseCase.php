<?php

namespace App\Application\Branch\Query;

use App\Application\Branch\Presenter\BranchPresenter;
use App\Domain\Supporting\Branch\Repository\BranchRepository;

final class GetAdminBranchUseCase
{
    public function __construct(
        private readonly BranchRepository $branches,
        private readonly BranchPresenter $presenter,
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
