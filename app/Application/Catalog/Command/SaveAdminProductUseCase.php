<?php

namespace App\Application\Catalog\Command;

use App\Domain\Catalog\Repository\CatalogItemRepository;

final class SaveAdminProductUseCase
{
    public function __construct(
        private readonly CatalogItemRepository $items,
    ) {}

    public function execute(
        ?int $id,
        string $name,
        int $price,
        ?int $oldPrice,
        ?int $weight,
        string $status,
        ?string $description,
        ?string $sku,
    ): int {
        $name = trim($name);
        if ($name === '') {
            throw new \InvalidArgumentException('Нужно название товара.');
        }
        if ($price < 0) {
            throw new \InvalidArgumentException('Цена не может быть отрицательной.');
        }

        $description = trim((string) $description);

        return $this->items->saveProduct(
            $id,
            $name,
            $price,
            $oldPrice,
            $weight,
            $status,
            $description !== '' ? $description : null,
            $sku,
        );
    }
}
