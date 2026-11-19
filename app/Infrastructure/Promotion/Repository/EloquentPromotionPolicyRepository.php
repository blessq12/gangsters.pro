<?php

namespace App\Infrastructure\Promotion\Repository;

use App\Domain\Core\Order\Policy\PromotionPolicy;
use App\Domain\Core\Order\Repository\PromotionPolicyRepository;
use App\Infrastructure\Promotion\Mapper\PromotionPolicyMapper;
use App\Infrastructure\Promotion\Model\PRM_Policy;

final class EloquentPromotionPolicyRepository implements PromotionPolicyRepository
{
    public function __construct(
        private readonly PromotionPolicyMapper $mapper,
    ) {}

    public function find(): ?PromotionPolicy
    {
        $row = PRM_Policy::query()->find(self::SINGLETON_ID);

        return $row instanceof PRM_Policy ? $this->mapper->toDomain($row) : null;
    }

    public function save(array $fields): PromotionPolicy
    {
        $row = PRM_Policy::query()->find(self::SINGLETON_ID);
        if (! $row instanceof PRM_Policy) {
            $row = new PRM_Policy();
            $row->id = self::SINGLETON_ID;
        }

        $row->fill($this->mapper->flatFieldsToPersistence($fields));
        $row->save();

        return $this->mapper->toDomain($row);
    }
}
