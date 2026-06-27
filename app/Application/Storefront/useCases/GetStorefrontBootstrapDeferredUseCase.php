<?php

namespace App\Application\Storefront\useCases;

use App\Application\Catalog\useCases\GetCatalogUseCase;
use App\Application\Company\useCases\GetCompanyDocumentsUseCase;
use App\Application\Company\useCases\GetCompanyLegalDataUseCase;
use App\Application\Delivery\useCases\GetDeliveryDataUseCase;
use App\Application\MarketingContent\useCases\GetMarketingContentUseCase;

/**
 * Deferred bootstrap: тяжёлые блоки после critical.
 */
final class GetStorefrontBootstrapDeferredUseCase
{
    public function __construct(
        private readonly GetCatalogUseCase $catalog,
        private readonly GetDeliveryDataUseCase $delivery,
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
            'catalog' => $this->catalog->execute(),
            'delivery' => $this->delivery->executeDeferredZone(),
            'company' => [
                'legals' => $this->companyLegals->execute()['data'],
                'documents' => $this->companyDocuments->execute()['data'],
            ],
            'marketing' => $this->marketing->executePromotionsOnly(),
        ];
    }
}
