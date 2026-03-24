<?php

namespace App\Domain\SystemContent\Repository;

use App\Domain\SystemContent\Entity\Document;

interface DocumentRepository
{
    /**
     * @return Document[]
     */
    public function findAllActiveOrdered(): array;
}

