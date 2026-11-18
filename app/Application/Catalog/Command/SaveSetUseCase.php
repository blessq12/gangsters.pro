<?php

namespace App\Application\Catalog\Command;

use App\Application\Catalog\DTO\SaveSetDto;
use App\Domain\Catalog\Enum\ProductStatus;
use App\Domain\Catalog\Event\CatalogItemPriceChanged;
use App\Domain\Catalog\Repository\CatalogItemRepository;

final class SaveSetUseCase
{
    public function __construct(
        private readonly CatalogItemRepository $items,
    ) {}

    /**
     * @return array{id: int}
     */
    public function execute(SaveSetDto $dto): array
    {
        $name = trim($dto->name);
        if ($name === '') {
            throw new \InvalidArgumentException('Нужно название набора.');
        }

        if ($dto->price < 0) {
            throw new \InvalidArgumentException('Цена не может быть отрицательной.');
        }

        $status = ProductStatus::tryFrom($dto->status);
        if ($status === null) {
            throw new \InvalidArgumentException('Некорректный статус набора.');
        }

        $sku = $dto->sku !== null ? trim($dto->sku) : null;

        $before = $dto->id !== null ? $this->items->findSetById($dto->id) : null;
        $priceBefore = $before?->regularPrice()?->amountKopecks();

        $id = $this->items->saveSet(
            $dto->id,
            $name,
            $sku !== '' ? $sku : null,
            $dto->price,
            $status->value,
        );

        $priceAfter = $this->items->findSetById($id)?->regularPrice()?->amountKopecks();
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
