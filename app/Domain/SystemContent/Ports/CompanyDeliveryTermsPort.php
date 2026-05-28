<?php

namespace App\Domain\SystemContent\Ports;

use App\Domain\SystemContent\ValueObjects\CompanyDeliveryTerms;

interface CompanyDeliveryTermsPort
{
    public function current(): CompanyDeliveryTerms;
}
