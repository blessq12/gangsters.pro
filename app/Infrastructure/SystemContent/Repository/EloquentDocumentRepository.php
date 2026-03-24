<?php

namespace App\Infrastructure\SystemContent\Repository;

use App\Domain\SystemContent\Entity\Document as DocumentEntity;
use App\Domain\SystemContent\Repository\DocumentRepository;
use App\Infrastructure\SystemContent\Model\SYS_Document;

final class EloquentDocumentRepository implements DocumentRepository
{
    public function findAllActiveOrdered(): array
    {
        return SYS_Document::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get()
            ->map(fn (SYS_Document $document) => new DocumentEntity(
                id: (int) $document->id,
                key: (string) $document->key,
                title: (string) $document->title,
                content: $document->content,
                isActive: (bool) $document->is_active,
            ))
            ->all();
    }
}

