<?php

namespace App\Application\Content\Query;

use App\Domain\Content\Repository\CompanyDocumentRepository;

final class ListCompanyDocumentsUseCase
{
    public function __construct(
        private readonly CompanyDocumentRepository $documents,
    ) {}

    /**
     * @return list<array{id: int, slug: string, name: string, content: string}>
     */
    public function execute(): array
    {
        $rows = [];
        foreach ($this->documents->findAllOrdered() as $document) {
            $rows[] = [
                'id' => $document->id(),
                'slug' => $document->slug(),
                'name' => $document->name(),
                'content' => $document->content() ?? '',
            ];
        }

        return $rows;
    }
}
