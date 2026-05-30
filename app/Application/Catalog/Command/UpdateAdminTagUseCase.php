<?php

namespace App\Application\Catalog\Command;

use App\Application\Catalog\Contracts\TagDictionaryRepository;
use App\Application\Catalog\DTO\UpdateAdminTagDTO;

final class UpdateAdminTagUseCase
{
    public function __construct(
        private readonly TagDictionaryRepository $tags,
    ) {
    }

    public function execute(UpdateAdminTagDTO $dto): array
    {
        $tag = $this->tags->update($dto);

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
