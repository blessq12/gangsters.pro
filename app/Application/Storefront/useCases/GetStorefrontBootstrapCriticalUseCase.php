<?php

namespace App\Application\Storefront\useCases;

use App\Application\Catalog\useCases\GetCatalogUseCase;
use App\Application\Promotion\useCases\GetPromotionPolicyUseCase;

/**
 * Critical bootstrap: минимум для Home и shell (без CMS/delivery content).
 */
final class GetStorefrontBootstrapCriticalUseCase
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
            'catalog' => $this->catalog->executeLite(),
            'promotion' => $this->promotion->execute()['data'],
        ];
    }
}
