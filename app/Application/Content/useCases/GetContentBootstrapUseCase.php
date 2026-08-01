<?php

namespace App\Application\Content\useCases;

/**
 * Single public content snapshot for the SPA.
 */
final class GetContentBootstrapUseCase
{
    public function __construct(
        private readonly GetCompanyDataUseCase $company,
        private readonly GetCompanyLegalDataUseCase $companyLegals,
        private readonly GetCompanyDocumentsUseCase $companyDocuments,
        private readonly GetMarketingContentUseCase $marketing,
        private readonly GetDeliveryDataUseCase $delivery,
    ) {}

    /**
     * @return array{
     *     version: string,
     *     company: array{main: mixed, legals: mixed, documents: mixed},
     *     marketing: array{banners: list<array<string, mixed>>, promotions: list<array<string, mixed>>},
     *     delivery: mixed
     * }
     */
    public function execute(): array
    {
        return [
            'version' => gmdate('c'),
            'company' => [
                'main' => $this->company->execute()['data'],
                'legals' => $this->companyLegals->execute()['data'],
                'documents' => $this->companyDocuments->execute()['data'],
            ],
            'marketing' => $this->marketing->execute(),
            'delivery' => $this->delivery->execute()['data'],
        ];
    }
}
