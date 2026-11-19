<?php

namespace App\Infrastructure\Company\Mapper;

use App\Domain\Supporting\Company\Entity\Kitchen;
use App\Infrastructure\Company\Model\CMP_Kitchen;

final class KitchenMapper
{
    public function toDomain(CMP_Kitchen $row): Kitchen
    {
        return new Kitchen(
            id: (int) $row->id,
            branchId: (int) $row->branch_id,
            city: $this->nullableString($row->city),
            street: $this->nullableString($row->street),
            house: $this->nullableString($row->house),
            comment: $this->nullableString($row->comment),
            latitude: $row->latitude !== null ? (float) $row->latitude : null,
            longitude: $row->longitude !== null ? (float) $row->longitude : null,
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
