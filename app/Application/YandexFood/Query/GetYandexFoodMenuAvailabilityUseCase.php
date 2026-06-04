<?php

namespace App\Application\YandexFood\Query;

use App\Application\Integrations\Contracts\IntegrationMenuExportReadPort;
use App\Application\YandexFood\DTO\YandexMenuAvailabilityRequestDto;

/**
 * Формат ответа — как у прежнего легаси-эндпоинта availability (items + modifiers).
 * список позиций с quantity = 0 для товаров вне статуса active (в легаси — visible = 0).
 */
final class GetYandexFoodMenuAvailabilityUseCase
{
    public function __construct(
        private readonly IntegrationMenuExportReadPort $menuExport,
    ) {
    }

    /**
     * @return array{items: list<array{id: string, quantity: int}>, modifiers: array<int, mixed>}
     */
    public function execute(YandexMenuAvailabilityRequestDto $dto): array
    {
        $unavailable = $this->menuExport->getUnavailableProductIds();

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
