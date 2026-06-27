<?php

namespace App\Application\Storefront\useCases;

use App\Application\Catalog\useCases\GetCatalogUseCase;
use App\Application\Company\useCases\GetCompanyDataUseCase;
use App\Application\Delivery\useCases\GetDeliveryDataUseCase;
use App\Application\MarketingContent\useCases\GetMarketingContentUseCase;
use App\Application\Promotion\useCases\GetPromotionPolicyUseCase;

/**
 * Critical bootstrap: минимум для Home и shell.
 */
final class GetStorefrontBootstrapCriticalUseCase
{
    public function __construct(
        private readonly GetCatalogUseCase $catalog,
        private readonly GetDeliveryDataUseCase $delivery,
        private readonly GetPromotionPolicyUseCase $promotion,
        private readonly GetCompanyDataUseCase $company,
        private readonly GetMarketingContentUseCase $marketing,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(): array
    {
        return [
            'version' => gmdate('c'),
            'catalog' => $this->catalog->executeLite(),
            'delivery' => $this->delivery->executeLite()['data'],
            'promotion' => $this->promotion->execute()['data'],
            'company' => [
                'main' => $this->company->execute()['data'],
            ],
            'marketing' => $this->marketing->executeBannersOnly(),
        ];
    }
}
