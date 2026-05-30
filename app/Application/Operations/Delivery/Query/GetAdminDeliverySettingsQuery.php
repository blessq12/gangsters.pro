<?php

namespace App\Application\Operations\Delivery\Query;

use App\Application\Common\Exceptions\ApiException;
use App\Domain\SystemContent\Repository\CompanyRepository;

final class GetAdminDeliverySettingsQuery
{
    public function __construct(
        private readonly CompanyRepository $companies,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(): array
    {
        $company = $this->companies->first();
        if ($company === null) {
            throw new ApiException('Company not found.', 404);
        }

        return [
            'company_id' => $company->id(),
            'company_name' => $company->name(),
            'delivery_zone_geojson' => $company->deliveryZoneGeojson(),
            'kitchen_latitude' => $company->kitchenLatitude(),
            'kitchen_longitude' => $company->kitchenLongitude(),
            'delivery_hours' => $company->deliveryHours(),
            'min_order_amount_kopecks' => $company->minOrderAmountKopecks(),
            'delivery_fee_kopecks' => $company->deliveryFeeKopecks(),
            'average_delivery_time_minutes' => $company->averageDeliveryTimeMinutes(),
        ];
    }
}
