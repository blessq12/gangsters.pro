<?php

namespace App\Application\Catalog\Command;

use App\Application\Catalog\DTO\SaveProductDto;
use App\Domain\Catalog\Enum\ProductStatus;
use App\Domain\Catalog\Repository\CatalogItemRepository;

final class SaveProductUseCase
{
    public function __construct(
        private readonly CatalogItemRepository $items,
    ) {}

    /**
     * @return array{id: int}
     */
    public function execute(SaveProductDto $dto): array
    {
        $name = trim($dto->name);
        if ($name === '') {
            throw new \InvalidArgumentException('Нужно название товара.');
        }

        if ($dto->price < 0) {
            throw new \InvalidArgumentException('Цена не может быть отрицательной.');
        }

        $status = ProductStatus::tryFrom($dto->status);
        if ($status === null) {
            throw new \InvalidArgumentException('Некорректный статус товара.');
        }

        $description = trim((string) $dto->description);
        $sku = $dto->sku !== null ? trim($dto->sku) : null;

        $id = $this->items->saveProduct(
            $dto->id,
            $name,
            $dto->price,
            $dto->weight,
            $status->value,
            $description !== '' ? $description : null,
            $sku !== '' ? $sku : null,
        );

        return ['id' => $id];
    }
}
