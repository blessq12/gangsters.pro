<?php

namespace App\Application\Content\Command;

use App\Application\Content\Query\GetOwnerCompanyUseCase;
use App\Domain\Content\Entity\Company;
use App\Domain\Content\Entity\CompanyLegalInfo;
use App\Domain\Content\Repository\CompanyLegalRepository;
use App\Domain\Content\Repository\CompanyRepository;
use App\Domain\Content\ValueObject\CompanySocials;

final class SaveOwnerCompanyUseCase
{
    /** @var list<string> */
    private const DAYS = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

    public function __construct(
        private readonly CompanyRepository $companies,
        private readonly CompanyLegalRepository $legal,
        private readonly GetOwnerCompanyUseCase $read,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function execute(array $payload): array
    {
        $name = trim((string) ($payload['name'] ?? ''));
        if ($name === '') {
            throw new \InvalidArgumentException('Нужно название компании.');
        }

        $schedule = [];
        $rawSchedule = is_array($payload['schedule'] ?? null) ? $payload['schedule'] : [];
        foreach (self::DAYS as $day) {
            $row = is_array($rawSchedule[$day] ?? null) ? $rawSchedule[$day] : [];
            $schedule[$day] = [
                'work' => $this->nullable($row['work'] ?? null),
                'is_day_off' => (bool) ($row['is_day_off'] ?? false),
            ];
        }

        $socials = is_array($payload['socials'] ?? null) ? $payload['socials'] : [];
        $legal = is_array($payload['legal'] ?? null) ? $payload['legal'] : [];

        $this->companies->save(new Company(
            id: CompanyRepository::SINGLETON_ID,
            name: $name,
            description: $this->nullable($payload['description'] ?? null),
            phone: $this->nullable($payload['phone'] ?? null),
            email: $this->nullable($payload['email'] ?? null),
            socials: new CompanySocials(
                telegram: $this->nullable($socials['telegram'] ?? null),
                vk: $this->nullable($socials['vk'] ?? null),
                inst: $this->nullable($socials['inst'] ?? null),
                siteUrl: $this->nullable($socials['site_url'] ?? null),
                whatsapp: $this->nullable($socials['whatsapp'] ?? null),
            ),
            schedule: $schedule,
        ));

        $currentLegal = $this->legal->findPublic();
        $this->legal->save(new CompanyLegalInfo(
            id: $currentLegal?->id() ?? 0,
            companyId: CompanyRepository::SINGLETON_ID,
            fullName: $this->nullable($legal['full_name'] ?? null),
            inn: $this->nullable($legal['inn'] ?? null),
            ogrn: $this->nullable($legal['ogrn'] ?? null),
        ));

        return $this->read->execute();
    }

    private function nullable(mixed $value): ?string
    {
        $trimmed = trim((string) ($value ?? ''));

        return $trimmed !== '' ? $trimmed : null;
    }
}
