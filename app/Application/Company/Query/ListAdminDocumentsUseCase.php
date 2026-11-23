<?php

namespace App\Application\Company\Query;

use App\Application\Company\Presenter\CompanyPresenter;
use App\Application\Shared\PageParams;
use App\Domain\Supporting\Company\Entity\CompanyDocument;
use App\Domain\Supporting\Company\Repository\CompanyDocumentRepository;

final class ListAdminDocumentsUseCase
{
    public function __construct(
        private readonly CompanyDocumentRepository $documents,
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
                fn (CompanyDocument $item): array => $this->presenter->document($item),
                $this->documents->listPage($offset, $limit),
            ),
            $this->documents->countAll(),
            $offset,
            $limit,
        );
    }
}
