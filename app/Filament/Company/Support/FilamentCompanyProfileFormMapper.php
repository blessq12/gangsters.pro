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
        return [
            ...$detail,
            'work_schedule' => $detail['work_schedule'] ?? [],
            'logo_upload' => null,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function toDto(array $data): UpdateCompanyProfileDto
    {
        $schedule = isset($data['work_schedule']) && is_array($data['work_schedule'])
            ? WorkScheduleNormalizer::normalizeForStorage($data['work_schedule'])
            : null;

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
            workSchedule: $schedule,
            telegram: $data['telegram'] ?? null,
            siteUrl: $data['site_url'] ?? null,
            vk: $data['vk'] ?? null,
            inst: $data['inst'] ?? null,
            logo: $data['logo'] ?? null,
        );
    }
}
