<?php

namespace App\Application\Order\Handler;

use App\Application\Order\Mapper\CheckoutConfirmedOrderSnapshotMapper;
use App\Application\Order\useCases\CreateOrderUseCase;
use App\Domain\Checkout\Event\CheckoutConfirmed;

final class OnCheckoutConfirmed
{
    public function __construct(
        private readonly CreateOrderUseCase $createOrder,
    ) {}

    public function handle(CheckoutConfirmed $event): void
    {
        $this->createOrder->execute(
            CheckoutConfirmedOrderSnapshotMapper::toCreateOrderDto($event),
        );
    }
}
