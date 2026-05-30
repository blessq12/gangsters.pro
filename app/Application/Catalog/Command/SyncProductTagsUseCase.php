<?php

namespace App\Application\Catalog\Command;

use App\Application\Catalog\Contracts\TagDictionaryRepository;
use App\Application\Catalog\DTO\SyncProductTagsDTO;
use App\Application\Catalog\Presenter\AdminProductPresenter;
use App\Application\Catalog\Support\CatalogEventPublisher;
use App\Application\Common\Exceptions\ApiException;
use App\Domain\Product\Repository\ProductRepository;
use App\Domain\Product\VO\ProductTag;

final class SyncProductTagsUseCase
{
    public function __construct(
        private readonly ProductRepository $products,
        private readonly TagDictionaryRepository $tags,
        private readonly AdminProductPresenter $presenter,
        private readonly CatalogEventPublisher $events,
    ) {
    }

    public function execute(SyncProductTagsDTO $dto): array
    {
        $product = $this->products->findById($dto->productId);
        if ($product === null) {
            throw new ApiException('Product not found.', 404);
        }

        $dictionary = [];
        foreach ($this->tags->listAll() as $tag) {
            $dictionary[$tag->code] = $tag;
        }

        $selected = [];
        foreach ($dto->tagCodes as $code) {
            if (! isset($dictionary[$code])) {
                continue;
            }
            $tag = $dictionary[$code];
            $selected[] = new ProductTag(
                code: $tag->code,
                label: $tag->label,
                color: $tag->color,
            );
        }

        $product->setTags($selected);
        $this->products->save($product);
        $this->events->productUpdated($product);

        return $this->presenter->presentDetail($product);
    }
}
