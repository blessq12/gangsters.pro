<?php

namespace App\Infrastructure\SystemContent\Repository;

use App\Domain\SystemContent\Entity\Company as CompanyEntity;
use App\Domain\SystemContent\Repository\CompanyRepository;
use App\Infrastructure\SystemContent\Model\SYS_Company;

final class EloquentCompanyRepository implements CompanyRepository
{
    public function first(): ?CompanyEntity
    {
        $company = SYS_Company::query()->first();

        if ($company === null) {
            return null;
        }

        return new CompanyEntity(
            id: (int) $company->id,
            name: $company->name,
            brandName: $company->brand_name,
            description: $company->description,
            tagline: $company->tagline,
            country: $company->country,
            state: $company->state,
            city: $company->city,
            street: $company->street,
            house: $company->house,
            addressComment: $company->address_comment,
            cityCoverage: $company->city_coverage,
            deliveryZoneGeojson: $company->delivery_zone_geojson,
            kitchenLatitude: $company->kitchen_latitude !== null ? (float) $company->kitchen_latitude : null,
            kitchenLongitude: $company->kitchen_longitude !== null ? (float) $company->kitchen_longitude : null,
            phone: $company->phone,
            phoneAdditional: $company->phone_additional,
            supportPhone: $company->support_phone,
            whatsappPhone: $company->whatsapp_phone,
            emailAddress: $company->email_address,
            publicEmail: $company->public_email,
            workHours: $company->work_hours,
            deliveryHours: $company->delivery_hours,
            workSchedule: $company->work_schedule,
            minOrderAmountKopecks: $company->min_order_amount_kopecks,
            deliveryFeeKopecks: $company->delivery_fee_kopecks,
            averageDeliveryTimeMinutes: $company->average_delivery_time_minutes,
            telegram: $company->telegram,
            siteUrl: $company->site_url,
            vk: $company->vk,
            inst: $company->inst,
        );
    }
}
