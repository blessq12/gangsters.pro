<?php

namespace App\Infrastructure\Company\Mapper;

use App\Domain\Supporting\Company\Entity\Branch;
use App\Domain\Supporting\Company\Support\ScheduleNormalizer;
use App\Infrastructure\Company\Model\BranchModel;

final class BranchMapper
{
    public function toDomain(BranchModel $row): Branch
    {
        return new Branch(
            id: (int) $row->id,
            companyId: (int) $row->company_id,
            name: (string) $row->name,
            isActive: (bool) $row->is_active,
            schedule: ScheduleNormalizer::normalize(
                is_array($row->schedule) ? $row->schedule : [],
            ),
            city: $this->nullableString($row->city),
            street: $this->nullableString($row->street),
            house: $this->nullableString($row->house),
            comment: $this->nullableString($row->comment),
            latitude: $row->latitude !== null ? (float) $row->latitude : null,
            longitude: $row->longitude !== null ? (float) $row->longitude : null,
            phone: $this->nullableString($row->phone),
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
