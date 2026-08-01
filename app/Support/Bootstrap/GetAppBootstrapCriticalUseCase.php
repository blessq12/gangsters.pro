<?php

namespace App\Support\Bootstrap;

use App\Application\Catalog\useCases\GetCatalogUseCase;
use App\Application\Promotion\useCases\GetPromotionPolicyUseCase;

/**
 * Critical SPA bootstrap: catalog lite + promotion policy.
 * Composition only — not a bounded context.
 */
final class GetAppBootstrapCriticalUseCase
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
