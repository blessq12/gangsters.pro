<?php

namespace App\Application\Content\useCases;

use App\Domain\Content\Entity\CompanyDocument;
use App\Domain\Content\Repository\CompanyDocumentRepository;

/**
 * Сценарий: получить публичные документы компании.
 */
final class GetCompanyDocumentsUseCase
{
    public function __construct(
        private readonly CompanyDocumentRepository $documents,
    ) {}

    /**
     * @return array{data: list<array<string, mixed>>}
     */
    public function execute(): array
    {
        return [
            'data' => array_map(
                fn (CompanyDocument $document) => $this->mapDocument($document),
                $this->documents->findAllOrdered(),
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapDocument(CompanyDocument $document): array
    {
        return [
            'id' => $document->id(),
            'key' => $document->key(),
            'title' => $document->title(),
            'content' => $document->content(),
        ];
    }
}
