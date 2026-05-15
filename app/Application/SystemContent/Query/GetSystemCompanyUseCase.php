<?php

namespace App\Application\SystemContent\Query;

use App\Domain\SystemContent\Repository\CompanyRepository;

final class GetSystemCompanyUseCase
{
    public function __construct(
        private readonly CompanyRepository $companies,
    ) {}

    public function execute(): array
    {
        $company = $this->companies->first();

        if ($company === null) {
            return ['data' => null];
        }

        return [
            'data' => [
                'id' => $company->id(),
                'name' => $company->name(),
                'brand_name' => $company->brandName(),
                'description' => $company->description(),
                'tagline' => $company->tagline(),
                'country' => $company->country(),
                'state' => $company->state(),
                'city' => $company->city(),
                'street' => $company->street(),
                'house' => $company->house(),
                'address_comment' => $company->addressComment(),
                'city_coverage' => $company->cityCoverage(),
                'delivery_zone_geojson' => $company->deliveryZoneGeojson(),
                'kitchen_latitude' => $company->kitchenLatitude(),
                'kitchen_longitude' => $company->kitchenLongitude(),
                'phone' => $company->phone(),
                'phone_additional' => $company->phoneAdditional(),
                'support_phone' => $company->supportPhone(),
                'whatsapp_phone' => $company->whatsappPhone(),
                'email_address' => $company->emailAddress(),
                'public_email' => $company->publicEmail(),
                'work_hours' => $company->workHours(),
                'work_schedule' => self::sanitizeWorkScheduleForPublic(
                    $company->workSchedule(),
                ),
                'min_order_amount_kopecks' => $company->minOrderAmountKopecks(),
                'delivery_fee_kopecks' => $company->deliveryFeeKopecks(),
                'average_delivery_time_minutes' => $company->averageDeliveryTimeMinutes(),
                'telegram' => $company->telegram(),
                'site_url' => $company->siteUrl(),
                'vk' => $company->vk(),
                'inst' => $company->inst(),
            ],
        ];
    }

    /**
     * Публичный контракт: только режим работы по дням, без слота доставки.
     *
     * @param  array<int, array<string, mixed>>|null  $schedule
     * @return array<int, array<string, mixed>>|null
     */
    private static function sanitizeWorkScheduleForPublic(?array $schedule): ?array
    {
        if ($schedule === null) {
            return null;
        }

        $out = [];
        foreach ($schedule as $row) {
            if (! is_array($row)) {
                continue;
            }
            unset($row['delivery']);
            $out[] = $row;
        }

        return $out;
    }
}
