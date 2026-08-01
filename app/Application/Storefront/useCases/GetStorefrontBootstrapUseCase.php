<?php

namespace App\Application\Storefront\useCases;

use App\Application\Catalog\useCases\GetCatalogUseCase;
use App\Application\Promotion\useCases\GetPromotionPolicyUseCase;

/**
 * Full storefront bootstrap for SPA (catalog + promotion policy).
 * CMS + delivery settings: GET /api/content/bootstrap.
 */
final class GetStorefrontBootstrapUseCase
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
