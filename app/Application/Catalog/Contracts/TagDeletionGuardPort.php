<?php

namespace App\Application\Catalog\Contracts;

interface TagDeletionGuardPort
{
    public function assertDeletable(int $tagId): void;
}
