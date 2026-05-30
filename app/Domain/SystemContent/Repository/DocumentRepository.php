<?php

namespace App\Domain\SystemContent\Repository;

use App\Domain\SystemContent\Entity\Document;

interface DocumentRepository
{
    /**
     * @return Document[]
     */
    public function findAllActiveOrdered(): array;

    /**
     * @return Document[]
     */
    public function findAllOrdered(): array;

    public function findById(int $id): ?Document;

    public function save(Document $document): Document;

    public function delete(int $id): void;
}

