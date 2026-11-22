<?php

namespace App\Application\Catalog\Command;

use App\Domain\Supporting\Catalog\Repository\CatalogItemRepository;

final class DeleteSetUseCase
{
    public function __construct(
        private readonly CatalogItemRepository $items,
    ) {}

    /**
     * @return array{ok: true}
     */
    public function execute(int $id): array
    {
        $this->items->deleteSet($id);

        return ['ok' => true];
    }
}
