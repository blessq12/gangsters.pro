<?php

namespace App\Application\SystemContent\Query;

use App\Domain\SystemContent\Entity\Document;
use App\Domain\SystemContent\Repository\DocumentRepository;

final class GetSystemDocumentsUseCase
{
    public function __construct(
        private readonly DocumentRepository $documents,
    ) {
    }

    public function execute(): array
    {
        return [
            'data' => array_map(
                fn (Document $document) => [
                    'id' => $document->id(),
                    'key' => $document->key(),
                    'title' => $document->title(),
                    'content' => $document->content(),
                    'is_active' => $document->isActive(),
                ],
                $this->documents->findAllActiveOrdered(),
            ),
        ];
    }
}

