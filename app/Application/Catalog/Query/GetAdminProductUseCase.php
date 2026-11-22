<?php

namespace App\Application\Catalog\Query;

use App\Application\Catalog\Presenter\CatalogPresenter;
use App\Domain\Supporting\Catalog\Repository\CatalogItemRepository;
use App\Domain\Supporting\Catalog\Repository\TechCardRepository;

final class GetAdminProductUseCase
{
    public function __construct(
        private readonly CatalogItemRepository $items,
        private readonly TechCardRepository $techCards,
        private readonly CatalogPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(int $id): array
    {
        $product = $this->items->findProductById($id);
        if ($product === null) {
            throw new \InvalidArgumentException('Товар не найден.');
        }

        return $this->presenter->adminProductDetail(
            $product,
            $this->items->listItemImages($id),
            $this->techCards->findByProductId($id),
        );
    }
}
