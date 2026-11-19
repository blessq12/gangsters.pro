<?php

namespace App\Application\Catalog\Query;

use App\Application\Catalog\Presenter\CatalogPresenter;
use App\Domain\Supporting\Catalog\Repository\CatalogItemRepository;

final class GetAdminSetUseCase
{
    public function __construct(
        private readonly CatalogItemRepository $items,
        private readonly CatalogPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(int $id): array
    {
        $set = $this->items->findSetById($id);
        if ($set === null) {
            throw new \InvalidArgumentException('Набор не найден.');
        }

        $names = $this->items->findProductNamesByIds(
            array_map(static fn ($line): int => $line->productId(), $set->lines()),
        );

        $lines = [];
        foreach ($set->lines() as $line) {
            $lines[] = [
                'product_id' => $line->productId(),
                'name' => $names[$line->productId()] ?? ('#'.$line->productId()),
                'quantity' => $line->quantity(),
            ];
        }

        return $this->presenter->adminSetDetail(
            $set,
            $lines,
            $this->items->listItemImages($id),
        );
    }
}
