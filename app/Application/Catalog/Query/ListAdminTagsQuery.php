<?php

namespace App\Application\Catalog\Query;

use App\Application\Catalog\Contracts\TagDictionaryRepository;

final class ListAdminTagsQuery
{
    public function __construct(
        private readonly TagDictionaryRepository $tags,
    ) {
    }

    public function execute(): array
    {
        return array_map(
            static fn ($tag) => [
                'id' => $tag->id,
                'code' => $tag->code,
                'label' => $tag->label,
                'color' => $tag->color,
                'is_active' => $tag->isActive,
                'sort_order' => $tag->sortOrder,
            ],
            $this->tags->listAll(),
        );
    }
}
