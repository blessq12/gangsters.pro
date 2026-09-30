<?php

namespace App\Infrastructure\Content\Mapper;

use App\Domain\Content\Entity\CompanyLegalInfo;
use App\Infrastructure\Content\Model\CMP_CompanyLegal;

final class CompanyLegalMapper
{
    public function toDomain(CMP_CompanyLegal $row): CompanyLegalInfo
    {
        return new CompanyLegalInfo(
            id: (int) $row->id,
            companyId: (int) $row->company_id,
            fullName: $this->nullableString($row->full_name),
            inn: $this->nullableString($row->inn),
            ogrn: $this->nullableString($row->ogrn),
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
