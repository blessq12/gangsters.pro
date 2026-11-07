<?php

namespace App\Application\Catalog\Command;

use App\Domain\Catalog\Repository\CatalogItemRepository;

final class SaveAdminSetUseCase
{
    public function __construct(
        private readonly CatalogItemRepository $items,
    ) {}

    public function execute(?int $id, string $name, ?string $sku, int $price, ?int $oldPrice, string $status): int
    {
        $name = trim($name);
        if ($name === '') {
            throw new \InvalidArgumentException('Нужно название набора.');
        }
        if ($price < 0) {
            throw new \InvalidArgumentException('Цена не может быть отрицательной.');
        }

        return $this->items->saveSet($id, $name, $sku, $price, $oldPrice, $status);
    }
}
