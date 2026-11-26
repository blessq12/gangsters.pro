<?php

namespace App\Application\Branch\Query;

use App\Application\Branch\Presenter\BranchPresenter;
use App\Application\Shared\PageParams;
use App\Domain\Supporting\Branch\Entity\Branch;
use App\Domain\Supporting\Branch\Repository\BranchRepository;

final class ListAdminBranchesUseCase
{
    public function __construct(
        private readonly BranchRepository $branches,
        private readonly BranchPresenter $presenter,
    ) {}

    /**
     * @return array{items: list<array<string, mixed>>, total: int, offset: int, limit: int}
     */
    public function execute(int $offset = 0, int $limit = PageParams::DEFAULT_LIMIT): array
    {
        ['offset' => $offset, 'limit' => $limit] = PageParams::normalize($offset, $limit);

        return PageParams::wrap(
            array_map(
                fn (Branch $item): array => $this->presenter->branch($item),
                $this->branches->listPageByCompany(Branch::DEFAULT_COMPANY_ID, $offset, $limit),
            ),
            $this->branches->countByCompany(Branch::DEFAULT_COMPANY_ID),
            $offset,
            $limit,
        );
    }
}
