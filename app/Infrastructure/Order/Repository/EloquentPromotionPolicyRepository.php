<?php

namespace App\Infrastructure\Order\Repository;

use App\Domain\Order\Entity\PromotionPolicy;
use App\Domain\Order\Repository\PromotionPolicyRepository;
use App\Infrastructure\Order\Mapper\PromotionPolicyMapper;
use App\Infrastructure\Order\Model\PRM_Configuration;

final class EloquentPromotionPolicyRepository implements PromotionPolicyRepository
{
    public function __construct(
        private readonly PromotionPolicyMapper $mapper,
    ) {}

    public function find(): ?PromotionPolicy
    {
        $row = PRM_Configuration::query()->find(self::SINGLETON_ID);

        return $row instanceof PRM_Configuration ? $this->mapper->toDomain($row) : null;
    }

    public function save(array $fields): PromotionPolicy
    {
        $row = PRM_Configuration::query()->find(self::SINGLETON_ID);
        if (! $row instanceof PRM_Configuration) {
            $row = new PRM_Configuration();
            $row->id = self::SINGLETON_ID;
        }

        $row->fill($fields);
        $row->save();

        return $this->mapper->toDomain($row);
    }
}
