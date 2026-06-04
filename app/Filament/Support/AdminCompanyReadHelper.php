<?php

namespace App\Filament\Support;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Company\Support\CompanyLogoUrlResolver;
use App\Infrastructure\SystemContent\Model\SYS_Company;
use App\Infrastructure\SystemContent\Model\SYS_CompanyLegal;

final class AdminCompanyReadHelper
{
    public function firstCompanyOrFail(): SYS_Company
    {
        $company = SYS_Company::query()->first();
        if ($company === null) {
            throw new ApiException('Company not found.', 404);
        }

        return $company;
    }

    /**
     * @return array<string, mixed>
     */
    public function deliverySettingsState(): array
    {
        $company = $this->firstCompanyOrFail();

        return [
            'company_id' => (int) $company->id,
            'company_name' => (string) $company->name,
            'delivery_zone_geojson' => $company->delivery_zone_geojson,
            'kitchen_latitude' => $company->kitchen_latitude,
            'kitchen_longitude' => $company->kitchen_longitude,
            'delivery_hours' => $company->delivery_hours,
            'min_order_amount_kopecks' => $company->min_order_amount_kopecks,
            'delivery_fee_kopecks' => $company->delivery_fee_kopecks,
            'average_delivery_time_minutes' => $company->average_delivery_time_minutes,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function profileState(): array
    {
        $company = $this->firstCompanyOrFail();

        return [
            'company_id' => (int) $company->id,
            'name' => (string) $company->name,
            'brand_name' => $company->brand_name,
            'description' => $company->description,
            'tagline' => $company->tagline,
            'country' => $company->country,
            'state' => $company->state,
            'city' => $company->city,
            'street' => $company->street,
            'house' => $company->house,
            'address_comment' => $company->address_comment,
            'city_coverage' => $company->city_coverage,
            'phone' => $company->phone,
            'phone_additional' => $company->phone_additional,
            'support_phone' => $company->support_phone,
            'whatsapp_phone' => $company->whatsapp_phone,
            'email_address' => $company->email_address,
            'public_email' => $company->public_email,
            'work_hours' => $company->work_hours,
            'work_schedule' => $company->work_schedule,
            'telegram' => $company->telegram,
            'site_url' => $company->site_url,
            'vk' => $company->vk,
            'inst' => $company->inst,
            'logo' => $company->logo,
            'logo_url' => CompanyLogoUrlResolver::resolve($company->logo),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function legalState(): array
    {
        $company = $this->firstCompanyOrFail();
        $legal = SYS_CompanyLegal::query()
            ->where('company_id', $company->id)
            ->first();

        if ($legal === null) {
            return [
                'id' => 0,
                'company_id' => (int) $company->id,
                'full_name' => null,
                'short_name' => null,
                'legal_form' => null,
                'legal_email' => null,
                'contracts_email' => null,
                'legal_phone' => null,
                'owner' => null,
                'responsible_person' => null,
                'responsible_position' => null,
                'inn' => null,
                'ogrn' => null,
                'ogrnip' => null,
                'okpo' => null,
                'kpp' => null,
                'tax_system' => null,
                'is_vat_payer' => false,
                'vat_rate_default' => null,
                'registration_address' => null,
                'actual_address' => null,
                'postal_address' => null,
                'bank_name' => null,
                'bik' => null,
                'checking_account' => null,
                'correspondent_account' => null,
            ];
        }

        return $legal->only([
            'id',
            'company_id',
            'full_name',
            'short_name',
            'legal_form',
            'legal_email',
            'contracts_email',
            'legal_phone',
            'owner',
            'responsible_person',
            'responsible_position',
            'inn',
            'ogrn',
            'ogrnip',
            'okpo',
            'kpp',
            'tax_system',
            'is_vat_payer',
            'vat_rate_default',
            'registration_address',
            'actual_address',
            'postal_address',
            'bank_name',
            'bik',
            'checking_account',
            'correspondent_account',
        ]);
    }
}
