<?php

namespace App\Infrastructure\Content\Mapper;

use App\Domain\Content\Entity\Company;
use App\Domain\Content\ValueObject\CompanySocials;
use App\Infrastructure\Content\Model\CMP_Company;

final class CompanyMapper
{
    private const DAYS = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

    public function toDomain(CMP_Company $row): Company
    {
        return new Company(
            id: (int) $row->id,
            name: (string) $row->name,
            description: $this->nullableString($row->description),
            phone: $this->nullableString($row->phone),
            email: $this->nullableString($row->email),
            socials: $this->mapSocials($row->socials),
            schedule: $this->mapSchedule($row->schedule),
        );
    }

    private function mapSocials(mixed $raw): CompanySocials
    {
        $data = is_array($raw) ? $raw : [];

        return new CompanySocials(
            telegram: $this->nullableString($data['telegram'] ?? null),
            vk: $this->nullableString($data['vk'] ?? null),
            inst: $this->nullableString($data['inst'] ?? null),
            siteUrl: $this->nullableString($data['site_url'] ?? null),
            whatsapp: $this->nullableString($data['whatsapp'] ?? null),
        );
    }

    /**
     * @return array<string, array{work: ?string, is_day_off: bool}>
     */
    private function mapSchedule(mixed $raw): array
    {
        $data = is_array($raw) ? $raw : [];
        $out = [];

        foreach (self::DAYS as $day) {
            $row = $data[$day] ?? null;
            if (! is_array($row)) {
                $out[$day] = [
                    'work' => null,
                    'is_day_off' => false,
                ];
                continue;
            }

            $out[$day] = [
                'work' => $this->nullableString($row['work'] ?? null),
                'is_day_off' => $row['is_day_off'] === true
                    || $row['is_day_off'] === 1
                    || $row['is_day_off'] === '1',
            ];
        }

        return $out;
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed !== '' ? $trimmed : null;
    }
}
