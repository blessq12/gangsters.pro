<?php

namespace App\Application\Content\Query;

use App\Domain\Content\Repository\CompanyLegalRepository;
use App\Domain\Content\Repository\CompanyRepository;

final class GetOwnerCompanyUseCase
{
    /** @var list<string> */
    private const DAYS = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

    public function __construct(
        private readonly CompanyRepository $companies,
        private readonly CompanyLegalRepository $legal,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(): array
    {
        $company = $this->companies->findPublic();
        $legal = $this->legal->findPublic();
        $schedule = [];
        foreach (self::DAYS as $day) {
            $row = $company?->schedule()[$day] ?? null;
            $schedule[$day] = [
                'work' => is_array($row) ? ($row['work'] ?? '') : '',
                'is_day_off' => is_array($row) ? (bool) ($row['is_day_off'] ?? false) : false,
            ];
        }

        return [
            'name' => $company?->name() ?? '',
            'description' => $company?->description() ?? '',
            'phone' => $company?->phone() ?? '',
            'email' => $company?->email() ?? '',
            'socials' => [
                'telegram' => $company?->socials()->telegram() ?? '',
                'vk' => $company?->socials()->vk() ?? '',
                'inst' => $company?->socials()->inst() ?? '',
                'site_url' => $company?->socials()->siteUrl() ?? '',
                'whatsapp' => $company?->socials()->whatsapp() ?? '',
            ],
            'schedule' => $schedule,
            'legal' => [
                'full_name' => $legal?->fullName() ?? '',
                'inn' => $legal?->inn() ?? '',
                'ogrn' => $legal?->ogrn() ?? '',
            ],
        ];
    }
}
