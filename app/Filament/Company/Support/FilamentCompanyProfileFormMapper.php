<?php

namespace App\Filament\Company\Support;

use App\Application\Company\Profile\DTO\UpdateCompanyProfileDto;
use App\Application\Company\Support\WorkScheduleNormalizer;

final class FilamentCompanyProfileFormMapper
{
    /**
     * @param  array<string, mixed>  $detail
     * @return array<string, mixed>
     */
    public static function toFormState(array $detail): array
    {
        $schedule = $detail['work_schedule'] ?? null;

        return [
            ...$detail,
            'work_schedule_json' => $schedule !== null
                ? json_encode($schedule, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
                : '',
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function toDto(array $data): UpdateCompanyProfileDto
    {
        $schedule = null;
        if (filled($data['work_schedule_json'] ?? null)) {
            $decoded = json_decode((string) $data['work_schedule_json'], true);
            $schedule = is_array($decoded)
                ? WorkScheduleNormalizer::normalizeForStorage($decoded)
                : null;
        }

        return new UpdateCompanyProfileDto(
            name: $data['name'] ?? null,
            brandName: $data['brand_name'] ?? null,
            description: $data['description'] ?? null,
            tagline: $data['tagline'] ?? null,
            country: $data['country'] ?? null,
            state: $data['state'] ?? null,
            city: $data['city'] ?? null,
            street: $data['street'] ?? null,
            house: $data['house'] ?? null,
            addressComment: $data['address_comment'] ?? null,
            cityCoverage: $data['city_coverage'] ?? null,
            phone: $data['phone'] ?? null,
            phoneAdditional: $data['phone_additional'] ?? null,
            supportPhone: $data['support_phone'] ?? null,
            whatsappPhone: $data['whatsapp_phone'] ?? null,
            emailAddress: $data['email_address'] ?? null,
            publicEmail: $data['public_email'] ?? null,
            workHours: $data['work_hours'] ?? null,
            deliveryHours: $data['delivery_hours'] ?? null,
            workSchedule: $schedule,
            minOrderAmountKopecks: isset($data['min_order_amount_kopecks'])
                ? (int) $data['min_order_amount_kopecks']
                : null,
            deliveryFeeKopecks: isset($data['delivery_fee_kopecks'])
                ? (int) $data['delivery_fee_kopecks']
                : null,
            averageDeliveryTimeMinutes: isset($data['average_delivery_time_minutes'])
                ? (int) $data['average_delivery_time_minutes']
                : null,
            telegram: $data['telegram'] ?? null,
            siteUrl: $data['site_url'] ?? null,
            vk: $data['vk'] ?? null,
            inst: $data['inst'] ?? null,
        );
    }
}
