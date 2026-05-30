<?php

namespace App\Application\Catalog\Query;

use App\Application\Catalog\Contracts\TagDictionaryRepository;
use App\Application\Catalog\DTO\AdminTagDTO;
use App\Application\Common\Exceptions\ApiException;

final class GetAdminTagDetailQuery
{
    public function __construct(
        private readonly TagDictionaryRepository $tags,
    ) {
    }

    public function execute(int $tagId): AdminTagDTO
    {
        $tag = $this->tags->findById($tagId);
        if ($tag === null) {
            throw new ApiException('Tag not found.', 404);
        }

        return $tag;
    }
}
