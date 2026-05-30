<?php

namespace App\Application\Company\Content\Document\Query;

use App\Domain\SystemContent\Entity\Document;
use App\Domain\SystemContent\Repository\DocumentRepository;

final class GetAdminDocumentListQuery
{
    public function __construct(
        private readonly DocumentRepository $documents,
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function execute(): array
    {
        return array_map(
            static fn (Document $document): array => [
                'id' => $document->id(),
                'key' => $document->key(),
                'title' => $document->title(),
                'content' => $document->content(),
                'is_active' => $document->isActive(),
            ],
            $this->documents->findAllOrdered(),
        );
    }
}
