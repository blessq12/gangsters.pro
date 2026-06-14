<?php

namespace App\Application\Company\useCases;

use App\Domain\Company\Entity\Company;
use App\Domain\Company\Repository\CompanyRepository;
use App\Domain\Company\ValueObject\CompanyContact;
use App\Domain\Company\ValueObject\CompanySchedule;
use App\Domain\Company\ValueObject\WorkScheduleRow;

/**
 * Сценарий: получить публичный профиль компании.
 */
final class GetCompanyDataUseCase
{
    public function __construct(
        private readonly CompanyRepository $company,
    ) {}

    /**
     * @return array{data: array<string, mixed>|null}
     */
    public function execute(): array
    {
        $company = $this->company->findPublic();

        return [
            'data' => $company instanceof Company
                ? $this->mapCompany($company)
                : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapCompany(Company $company): array
    {
        return [
            'id' => $company->id(),
            'name' => $company->name(),
            'brand_name' => $company->brandName(),
            'description' => $company->description(),
            'tagline' => $company->tagline(),
            ...$this->mapContact($company->contact()),
            ...$this->mapSchedule($company->schedule()),
            'logo' => $company->logo(),
            'telegram' => $company->telegram(),
            'site_url' => $company->siteUrl(),
            'vk' => $company->vk(),
            'inst' => $company->inst(),
        ];
    }

    /**
     * @return array<string, string|null>
     */
    private function mapContact(CompanyContact $contact): array
    {
        return [
            'phone' => $contact->phone(),
            'phone_additional' => $contact->phoneAdditional(),
            'support_phone' => $contact->supportPhone(),
            'whatsapp_phone' => $contact->whatsappPhone(),
            'email_address' => $contact->emailAddress(),
            'public_email' => $contact->publicEmail(),
        ];
    }

    /**
     * @return array{work_hours: string|null, work_schedule: list<array<string, mixed>>}
     */
    private function mapSchedule(CompanySchedule $schedule): array
    {
        return [
            'work_hours' => $schedule->workHours(),
            'work_schedule' => array_map(
                fn (WorkScheduleRow $row) => [
                    'day' => $row->day(),
                    'work' => $row->work(),
                    'is_day_off' => $row->isDayOff(),
                ],
                $schedule->workSchedule(),
            ),
        ];
    }
}
