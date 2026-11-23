<?php

namespace App\Infrastructure\Marketing\Repository;

use App\Domain\Supporting\Company\Entity\DeliveryConditionsText;
use App\Domain\Supporting\Company\Repository\DeliveryConditionsTextRepository;
use App\Infrastructure\Marketing\Mapper\DeliveryConditionsMapper;
use App\Infrastructure\Marketing\Model\MarketingDeliveryConditionsModel;

final class EloquentDeliveryConditionsRepository implements DeliveryConditionsTextRepository
{
    public function __construct(
        private readonly DeliveryConditionsMapper $mapper,
    ) {}

    public function findCurrent(): ?DeliveryConditionsText
    {
        $row = MarketingDeliveryConditionsModel::query()
            ->orderBy('id')
            ->first();

        return $row instanceof MarketingDeliveryConditionsModel
            ? $this->mapper->toDomain($row)
            : null;
    }

    public function findActive(): ?DeliveryConditionsText
    {
        $row = MarketingDeliveryConditionsModel::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->first();

        return $row instanceof MarketingDeliveryConditionsModel
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
            $row = MarketingDeliveryConditionsModel::query()->find($id);
            if (! $row instanceof MarketingDeliveryConditionsModel) {
                throw new \InvalidArgumentException('Условия доставки не найдены.');
            }
        } else {
            $row = MarketingDeliveryConditionsModel::query()->orderBy('id')->first();
            if (! $row instanceof MarketingDeliveryConditionsModel) {
                $row = new MarketingDeliveryConditionsModel();
            }
        }

        $row->body = $trimmed;
        $row->is_active = $isActive;
        $row->save();

        return $this->mapper->toDomain($row);
    }
}
