<?php

namespace App\Application\Company\Content\Document\Command;

use App\Domain\SystemContent\Entity\Document;
use App\Domain\SystemContent\Repository\DocumentRepository;

final class SaveDocumentUseCase
{
    public function __construct(
        private readonly DocumentRepository $documents,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function execute(array $data): array
    {
        $document = new Document(
            id: (int) ($data['id'] ?? 0),
            key: (string) ($data['key'] ?? ''),
            title: (string) ($data['title'] ?? ''),
            content: $data['content'] ?? null,
            isActive: (bool) ($data['is_active'] ?? true),
        );

        $saved = $this->documents->save($document);

        return [
            'id' => $saved->id(),
            'key' => $saved->key(),
            'title' => $saved->title(),
            'content' => $saved->content(),
            'is_active' => $saved->isActive(),
        ];
    }
}
