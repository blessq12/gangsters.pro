<?php

namespace App\Application\Catalog\Command;

use App\Application\Catalog\Contracts\TagDeletionGuardPort;
use App\Application\Catalog\Contracts\TagDictionaryRepository;
use App\Application\Common\Exceptions\ApiException;

final class DeleteTagUseCase
{
    public function __construct(
        private readonly TagDictionaryRepository $tags,
        private readonly TagDeletionGuardPort $guard,
    ) {
    }

    public function execute(int $tagId): void
    {
        if ($this->tags->findById($tagId) === null) {
            throw new ApiException('Tag not found.', 404);
        }

        $this->guard->assertDeletable($tagId);
        $this->tags->delete($tagId);
    }
}
