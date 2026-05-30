<?php

namespace App\Application\Company\Profile\Presenter;

use App\Application\Company\Support\CompanyLogoUrlResolver;
use App\Domain\SystemContent\Entity\Company;

final class AdminCompanyProfilePresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function present(Company $company): array
    {
        return [
            'company_id' => $company->id(),
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
            'phone' => $company->phone(),
            'phone_additional' => $company->phoneAdditional(),
            'support_phone' => $company->supportPhone(),
            'whatsapp_phone' => $company->whatsappPhone(),
            'email_address' => $company->emailAddress(),
            'public_email' => $company->publicEmail(),
            'work_hours' => $company->workHours(),
            'work_schedule' => $company->workSchedule(),
            'telegram' => $company->telegram(),
            'site_url' => $company->siteUrl(),
            'vk' => $company->vk(),
            'inst' => $company->inst(),
            'logo' => $company->logo(),
            'logo_url' => CompanyLogoUrlResolver::resolve($company->logo()),
        ];
    }
}
