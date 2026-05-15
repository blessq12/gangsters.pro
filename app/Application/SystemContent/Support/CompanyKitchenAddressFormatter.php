<?php

namespace App\Application\SystemContent\Support;

use App\Domain\SystemContent\Entity\Company;
use App\Infrastructure\SystemContent\Model\SYS_Company;

final class CompanyKitchenAddressFormatter
{
    public static function format(SYS_Company|Company $company): string
    {
        if ($company instanceof Company) {
            return self::fromParts(
                $company->country(),
                $company->state(),
                $company->city(),
                $company->street(),
                $company->house(),
            );
        }

        return self::fromParts(
            $company->country,
            $company->state,
            $company->city,
            $company->street,
            $company->house,
        );
    }

    private static function fromParts(
        ?string $country,
        ?string $state,
        ?string $city,
        ?string $street,
        ?string $house,
    ): string {
        $parts = array_filter([
            $country,
            $state,
            $city,
            $street,
            $house,
        ], static fn (?string $part) => is_string($part) && trim($part) !== '');

        return implode(', ', $parts);
    }
}
