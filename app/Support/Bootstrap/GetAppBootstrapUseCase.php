<?php

namespace App\Support\Bootstrap;

use App\Application\Catalog\useCases\GetCatalogUseCase;
use App\Application\Promotion\useCases\GetPromotionPolicyUseCase;

/**
 * Full SPA bootstrap (catalog + promotion policy).
 */
final class GetAppBootstrapUseCase
{
    public function __construct(
        private readonly GetCatalogUseCase $catalog,
        private readonly GetPromotionPolicyUseCase $promotion,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(): array
    {
        return [
            'version' => gmdate('c'),
            'catalog' => $this->catalog->execute(),
            'promotion' => $this->promotion->execute()['data'],
        ];
    }
}
