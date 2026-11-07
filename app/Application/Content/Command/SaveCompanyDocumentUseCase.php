<?php

namespace App\Application\Content\Command;

use App\Domain\Content\Repository\CompanyDocumentRepository;

final class SaveCompanyDocumentUseCase
{
    public function __construct(
        private readonly CompanyDocumentRepository $documents,
    ) {}

    /**
     * @return array{id: int, slug: string, name: string, content: string}
     */
    public function execute(?int $id, string $name, ?string $content): array
    {
        $name = trim($name);
        if ($name === '') {
            throw new \InvalidArgumentException('Нужно название документа.');
        }

        $content = trim((string) $content);
        $document = $this->documents->save($id, $name, $content !== '' ? $content : null);

        return [
            'id' => $document->id(),
            'slug' => $document->slug(),
            'name' => $document->name(),
            'content' => $document->content() ?? '',
        ];
    }
}
