<?php

namespace App\Application\YandexFood\Query;

use App\Application\Catalog\Contracts\CatalogYandexReadModelContract;
use App\Application\YandexFood\DTO\YandexMenuAvailabilityRequestDto;
use App\Application\YandexFood\YandexFoodBaseUseCase;

/**
 * Формат ответа — как у прежнего легаси-эндпоинта availability (items + modifiers).
 * список позиций с quantity = 0 для товаров вне статуса active (в легаси — visible = 0).
 */
final class GetYandexFoodMenuAvailabilityUseCase extends YandexFoodBaseUseCase
{
    public function __construct(
        private readonly CatalogYandexReadModelContract $catalogReadModel,
    ) {}

    /**
     * @return array{items: list<array{id: string, quantity: int}>, modifiers: array<int, mixed>}
     */
    public function execute(YandexMenuAvailabilityRequestDto $dto): array
    {
        $unavailable = $this->catalogReadModel->getUnavailableProductIds();

        return [
            'items' => array_map(
                static fn (string $productId): array => [
                    'id' => $productId,
                    'quantity' => 0,
                ],
                $unavailable,
            ),
            'modifiers' => [],
        ];
    }
}
