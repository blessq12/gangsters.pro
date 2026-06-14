<?php

namespace App\Domain\Checkout\Repository;

use App\Domain\Checkout\Entity\Checkout;
use App\Domain\Checkout\ValueObject\CheckoutId;

interface CheckoutRepository
{
    public function findById(CheckoutId $id): ?Checkout;

    public function save(Checkout $checkout): void;
}
