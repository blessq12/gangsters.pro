<?php

namespace App\Infrastructure\Company\Mapper;

use App\Domain\Supporting\Company\Entity\Company;
use App\Domain\Supporting\Company\ValueObject\CompanySocials;
use App\Infrastructure\Company\Model\CompanyModel;

final class CompanyMapper
{
    public function toDomain(CompanyModel $row): Company
    {
        return new Company(
            id: (int) $row->id,
            name: (string) $row->name,
            description: $this->nullableString($row->description),
            phone: $this->nullableString($row->phone),
            email: $this->nullableString($row->email),
            socials: $this->mapSocials($row->socials),
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

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed !== '' ? $trimmed : null;
    }
}
