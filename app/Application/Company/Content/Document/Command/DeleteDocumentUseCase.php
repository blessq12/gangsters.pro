<?php

namespace App\Application\Company\Content\Document\Command;

use App\Domain\SystemContent\Repository\DocumentRepository;

final class DeleteDocumentUseCase
{
    public function __construct(
        private readonly DocumentRepository $documents,
    ) {
    }

    public function execute(int $id): void
    {
        $this->documents->delete($id);
    }
}
