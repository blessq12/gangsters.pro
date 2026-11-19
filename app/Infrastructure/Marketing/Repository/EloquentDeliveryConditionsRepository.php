<?php

namespace App\Infrastructure\Marketing\Repository;

use App\Domain\Supporting\Company\Entity\DeliveryConditionsText;
use App\Domain\Supporting\Company\Repository\DeliveryConditionsTextRepository;
use App\Infrastructure\Marketing\Mapper\DeliveryConditionsMapper;
use App\Infrastructure\Marketing\Model\MKT_DeliveryConditions;

final class EloquentDeliveryConditionsRepository implements DeliveryConditionsTextRepository
{
    public function __construct(
        private readonly DeliveryConditionsMapper $mapper,
    ) {}

    public function findCurrent(): ?DeliveryConditionsText
    {
        $row = MKT_DeliveryConditions::query()
            ->orderBy('id')
            ->first();

        return $row instanceof MKT_DeliveryConditions
            ? $this->mapper->toDomain($row)
            : null;
    }

    public function findActive(): ?DeliveryConditionsText
    {
        $row = MKT_DeliveryConditions::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->first();

        return $row instanceof MKT_DeliveryConditions
            ? $this->mapper->toDomain($row)
            : null;
    }

    public function save(?int $id, string $body, bool $isActive): DeliveryConditionsText
    {
        $trimmed = trim($body);
        if ($trimmed === '') {
            throw new \InvalidArgumentException('Текст условий доставки обязателен.');
        }

        if ($id !== null) {
            $row = MKT_DeliveryConditions::query()->find($id);
            if (! $row instanceof MKT_DeliveryConditions) {
                throw new \InvalidArgumentException('Условия доставки не найдены.');
            }
        } else {
            $row = MKT_DeliveryConditions::query()->orderBy('id')->first();
            if (! $row instanceof MKT_DeliveryConditions) {
                $row = new MKT_DeliveryConditions();
            }
        }

        $row->body = $trimmed;
        $row->is_active = $isActive;
        $row->save();

        return $this->mapper->toDomain($row);
    }
}
