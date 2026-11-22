<?php

namespace App\Application\Company\Query;

use App\Application\Company\Presenter\CompanyPresenter;
use App\Domain\Supporting\Company\Repository\CompanyDocumentRepository;

final class GetAdminDocumentUseCase
{
    public function __construct(
        private readonly CompanyDocumentRepository $documents,
        private readonly CompanyPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(int $id): array
    {
        $document = $this->documents->findById($id);
        if ($document === null) {
            throw new \InvalidArgumentException('Документ не найден.');
        }

        return $this->presenter->document($document);
    }
}
