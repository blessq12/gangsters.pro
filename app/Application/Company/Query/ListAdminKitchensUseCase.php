<?php

namespace App\Application\Company\Query;

use App\Application\Company\Presenter\CompanyPresenter;
use App\Application\Shared\PageParams;
use App\Domain\Supporting\Company\Entity\Kitchen;
use App\Domain\Supporting\Company\Repository\KitchenRepository;
use App\Domain\Supporting\Company\Repository\CompanyRepository;

final class ListAdminKitchensUseCase
{
    public function __construct(
        private readonly KitchenRepository $kitchens,
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
                fn (Kitchen $item): array => $this->presenter->kitchen($item),
                $this->kitchens->listPageByCompany(CompanyRepository::SINGLETON_ID, $offset, $limit),
            ),
            $this->kitchens->countByCompany(CompanyRepository::SINGLETON_ID),
            $offset,
            $limit,
        );
    }
}
