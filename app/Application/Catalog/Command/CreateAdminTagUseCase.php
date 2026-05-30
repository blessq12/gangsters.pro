<?php

namespace App\Application\Catalog\Command;

use App\Application\Catalog\Contracts\TagDictionaryRepository;
use App\Application\Catalog\DTO\CreateAdminTagDTO;

final class CreateAdminTagUseCase
{
    public function __construct(
        private readonly TagDictionaryRepository $tags,
    ) {
    }

    public function execute(CreateAdminTagDTO $dto): array
    {
        $tag = $this->tags->create($dto);

        return [
            'id' => $tag->id,
            'code' => $tag->code,
            'label' => $tag->label,
            'color' => $tag->color,
            'is_active' => $tag->isActive,
            'sort_order' => $tag->sortOrder,
        ];
    }
}
