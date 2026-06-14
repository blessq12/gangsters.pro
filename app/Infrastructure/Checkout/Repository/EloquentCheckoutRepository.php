<?php

namespace App\Infrastructure\Checkout\Repository;

use App\Domain\Checkout\Entity\Checkout;
use App\Domain\Checkout\Repository\CheckoutRepository;
use App\Domain\Checkout\ValueObject\CheckoutId;
use App\Infrastructure\Checkout\Mapper\CheckoutMapper;
use App\Infrastructure\Checkout\Model\CHK_Checkout;

final class EloquentCheckoutRepository implements CheckoutRepository
{
    public function __construct(
        private readonly CheckoutMapper $mapper,
    ) {}

    public function findById(CheckoutId $id): ?Checkout
    {
        $row = CHK_Checkout::query()->find($id->value());

        return $row instanceof CHK_Checkout ? $this->mapper->toDomain($row) : null;
    }

    public function save(Checkout $checkout): void
    {
        $payload = $this->mapper->toPersistence($checkout);

        CHK_Checkout::query()->updateOrCreate(
            ['id' => $payload['id']],
            $payload,
        );
    }
}
