<?php

namespace App\Infrastructure\SystemContent\Ports;

use App\Domain\SystemContent\Ports\CompanyDeliveryTermsPort;
use App\Domain\SystemContent\Repository\CompanyRepository;
use App\Domain\SystemContent\ValueObjects\CompanyDeliveryTerms;

final class CompanyDeliveryTermsPortImpl implements CompanyDeliveryTermsPort
{
    public function __construct(
        private readonly CompanyRepository $companies,
    ) {}

    public function current(): CompanyDeliveryTerms
    {
        $company = $this->companies->first();

        if ($company === null) {
            return new CompanyDeliveryTerms(null, null);
        }

        return new CompanyDeliveryTerms(
            freeDeliveryThresholdKopecks: $company->minOrderAmountKopecks(),
            deliveryFeeKopecks: $company->deliveryFeeKopecks(),
        );
    }
}
