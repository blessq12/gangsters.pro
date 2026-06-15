<?php

namespace App\Application\Storefront\useCases;

use App\Application\Catalog\useCases\GetCatalogUseCase;
use App\Application\Company\useCases\GetCompanyDataUseCase;
use App\Application\Company\useCases\GetCompanyDocumentsUseCase;
use App\Application\Company\useCases\GetCompanyLegalDataUseCase;
use App\Application\Delivery\useCases\GetDeliveryDataUseCase;
use App\Application\MarketingContent\useCases\GetMarketingContentUseCase;
use App\Application\Promotion\useCases\GetPromotionPolicyUseCase;

/**
 * Сценарий: единый bootstrap витрины для SPA.
 */
final class GetStorefrontBootstrapUseCase
{
    public function __construct(
        private readonly GetCatalogUseCase $catalog,
        private readonly GetDeliveryDataUseCase $delivery,
        private readonly GetPromotionPolicyUseCase $promotion,
        private readonly GetCompanyDataUseCase $company,
        private readonly GetCompanyLegalDataUseCase $companyLegals,
        private readonly GetCompanyDocumentsUseCase $companyDocuments,
        private readonly GetMarketingContentUseCase $marketing,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(): array
    {
        return [
            'version' => gmdate('c'),
            'catalog' => $this->catalog->execute(),
            'delivery' => $this->delivery->execute()['data'],
            'promotion' => $this->promotion->execute()['data'],
            'company' => [
                'main' => $this->company->execute()['data'],
                'legals' => $this->companyLegals->execute()['data'],
                'documents' => $this->companyDocuments->execute()['data'],
            ],
            'marketing' => $this->marketing->execute(),
        ];
    }
}
