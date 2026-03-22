<?php

namespace App\Application\YandexFood\Query;

use App\Application\YandexFood\DTO\YandexMenuAvailabilityRequestDto;
use App\Application\YandexFood\YandexFoodBaseUseCase;
use App\Domain\Product\Entity\Product;

/**
 * Формат ответа — как у прежнего легаси-эндпоинта availability (items + modifiers).
 * список позиций с quantity = 0 для товаров вне статуса active (в легаси — visible = 0).
 */
final class GetYandexFoodMenuAvailabilityUseCase extends YandexFoodBaseUseCase
{
    /**
     * @return array{items: list<array{id: string, quantity: int}>, modifiers: array<int, mixed>}
     */
    public function execute(YandexMenuAvailabilityRequestDto $dto): array
    {
        $unavailable = array_values(array_filter(
            $this->products->findNonActive(),
            static fn (Product $p) => $p->id() !== null,
        ));

        return [
            'items' => array_map(
                static fn (Product $product): array => [
                    'id' => (string) $product->id(),
                    'quantity' => 0,
                ],
                $unavailable,
            ),
            'modifiers' => [],
        ];
    }
}
