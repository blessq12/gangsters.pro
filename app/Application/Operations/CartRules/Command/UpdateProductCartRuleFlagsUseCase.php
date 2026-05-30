<?php

namespace App\Application\Operations\CartRules\Command;

use App\Application\Catalog\DTO\UpdateCartRuleFlagsDTO;
use App\Application\Catalog\Presenter\AdminProductPresenter;
use App\Application\Catalog\Support\CatalogEventPublisher;
use App\Application\Common\Exceptions\ApiException;
use App\Application\Operations\CartRules\Contracts\UpdateProductCartRuleFlagsContract;
use App\Domain\Product\Repository\ProductRepository;

final class UpdateProductCartRuleFlagsUseCase implements UpdateProductCartRuleFlagsContract
{
    public function __construct(
        private readonly ProductRepository $products,
        private readonly AdminProductPresenter $presenter,
        private readonly CatalogEventPublisher $events,
    ) {
    }

    public function execute(UpdateCartRuleFlagsDTO $dto): array
    {
        $product = $this->products->findById($dto->productId);
        if ($product === null) {
            throw new ApiException('Product not found.', 404);
        }

        $product->setCartRuleCountsAsRoll($dto->countsAsRoll);
        $product->setCartRuleGiftCandidate($dto->giftCandidate);
        $product->setCartRuleIsComplementSet($dto->isComplementSet);
        $this->products->save($product);
        $this->events->productUpdated($product);

        return $this->presenter->presentDetail($product);
    }
}
