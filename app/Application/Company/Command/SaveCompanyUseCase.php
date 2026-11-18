<?php

namespace App\Application\Company\Command;

use App\Application\Company\DTO\SaveCompanyDto;
use App\Application\Company\Query\GetAdminCompanyUseCase;
use App\Domain\Company\Entity\Company;
use App\Domain\Company\Entity\CompanyLegalInfo;
use App\Domain\Company\Repository\CompanyLegalRepository;
use App\Domain\Company\Repository\CompanyRepository;
use App\Domain\Company\ValueObject\CompanySocials;
use App\Shared\ValueObject\PhoneNumber;

final class SaveCompanyUseCase
{
    /** @var list<string> */
    private const DAYS = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

    public function __construct(
        private readonly CompanyRepository $companies,
        private readonly CompanyLegalRepository $legal,
        private readonly GetAdminCompanyUseCase $read,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(SaveCompanyDto $dto): array
    {
        $name = trim($dto->name);
        if ($name === '') {
            throw new \InvalidArgumentException('Нужно название компании.');
        }

        $phone = $this->nullable($dto->phone);
        if ($phone !== null && PhoneNumber::tryFromRaw($phone) === null) {
            throw new \InvalidArgumentException('Некорректный телефон компании.');
        }

        $email = $this->nullable($dto->email);
        if ($email !== null && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Некорректный email компании.');
        }

        $inn = $this->nullable($dto->legal['inn'] ?? null);
        if ($inn !== null && ! $this->isValidInn($inn)) {
            throw new \InvalidArgumentException('Некорректный ИНН.');
        }

        $ogrn = $this->nullable($dto->legal['ogrn'] ?? null);
        if ($ogrn !== null && ! preg_match('/^\d{13}$|^\d{15}$/', $ogrn)) {
            throw new \InvalidArgumentException('Некорректный ОГРН.');
        }

        $whatsapp = $this->nullable($dto->socials['whatsapp'] ?? null);
        if ($whatsapp !== null && PhoneNumber::tryFromRaw($whatsapp) === null) {
            throw new \InvalidArgumentException('Некорректный WhatsApp.');
        }

        $this->companies->save(new Company(
            id: CompanyRepository::SINGLETON_ID,
            name: $name,
            description: $this->nullable($dto->description),
            phone: $phone,
            email: $email,
            socials: new CompanySocials(
                telegram: $this->nullable($dto->socials['telegram'] ?? null),
                vk: $this->nullable($dto->socials['vk'] ?? null),
                inst: $this->nullable($dto->socials['inst'] ?? null),
                siteUrl: $this->nullable($dto->socials['site_url'] ?? null),
                whatsapp: $whatsapp,
            ),
            schedule: $this->normalizeSchedule($dto->schedule),
        ));

        $currentLegal = $this->legal->findPublic();
        $this->legal->save(new CompanyLegalInfo(
            id: $currentLegal?->id() ?? 0,
            companyId: CompanyRepository::SINGLETON_ID,
            fullName: $this->nullable($dto->legal['full_name'] ?? null),
            inn: $inn,
            ogrn: $ogrn,
        ));

        return $this->read->execute();
    }

    /**
     * @param  array<string, mixed>  $rawSchedule
     * @return array<string, array{work: ?string, is_day_off: bool}>
     */
    private function normalizeSchedule(array $rawSchedule): array
    {
        $schedule = [];
        foreach (self::DAYS as $day) {
            $row = is_array($rawSchedule[$day] ?? null) ? $rawSchedule[$day] : [];
            $schedule[$day] = [
                'work' => $this->nullable($row['work'] ?? null),
                'is_day_off' => (bool) ($row['is_day_off'] ?? false),
            ];
        }

        return $schedule;
    }

    private function isValidInn(string $inn): bool
    {
        return (bool) preg_match('/^\d{10}$|^\d{12}$/', $inn);
    }

    private function nullable(?string $value): ?string
    {
        $trimmed = trim((string) ($value ?? ''));

        return $trimmed !== '' ? $trimmed : null;
    }
}
