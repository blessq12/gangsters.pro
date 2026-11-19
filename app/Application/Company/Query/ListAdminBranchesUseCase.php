<?php

namespace App\Application\Company\Query;

use App\Application\Company\Presenter\CompanyPresenter;
use App\Application\Shared\PageParams;
use App\Domain\Supporting\Company\Entity\Branch;
use App\Domain\Supporting\Company\Repository\BranchRepository;
use App\Domain\Supporting\Company\Repository\CompanyRepository;

final class ListAdminBranchesUseCase
{
    public function __construct(
        private readonly BranchRepository $branches,
        private readonly CompanyPresenter $presenter,
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
                $this->branches->listPageByCompany(CompanyRepository::SINGLETON_ID, $offset, $limit),
            ),
            $this->branches->countByCompany(CompanyRepository::SINGLETON_ID),
            $offset,
            $limit,
        );
    }
}
