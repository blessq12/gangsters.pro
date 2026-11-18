<?php

namespace App\Application\Catalog\Command;

use App\Application\Catalog\DTO\SaveProductDto;
use App\Domain\Catalog\Enum\ProductStatus;
use App\Domain\Catalog\Event\CatalogItemPriceChanged;
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

        $before = $dto->id !== null ? $this->items->findProductById($dto->id) : null;
        $priceBefore = $before?->regularPrice()?->amountKopecks();

        $id = $this->items->saveProduct(
            $dto->id,
            $name,
            $dto->price,
            $dto->weight,
            $status->value,
            $description !== '' ? $description : null,
            $sku !== '' ? $sku : null,
        );

        $priceAfter = $this->items->findProductById($id)?->regularPrice()?->amountKopecks();
        if ($before !== null && $priceAfter !== null && $priceAfter !== $priceBefore) {
            event(new CatalogItemPriceChanged(
                catalogItemId: $id,
                name: $name,
                regularPriceKopecks: $priceAfter,
            ));
        }

        return ['id' => $id];
    }
}
