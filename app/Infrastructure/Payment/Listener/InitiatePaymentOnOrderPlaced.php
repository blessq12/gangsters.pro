<?php

namespace App\Infrastructure\Payment\Listener;

use App\Application\Payment\Command\InitiatePaymentUseCase;
use App\Application\Payment\DTO\InitiatePaymentDto;
use App\Domain\Order\Event\OrderPlaced;

/**
 * Order публикует оформление; Payment заводит ожидающий платёж.
 */
final class InitiatePaymentOnOrderPlaced
{
    public function __construct(
        private readonly InitiatePaymentUseCase $initiate,
    ) {}

    public function handle(OrderPlaced $event): void
    {
        $this->initiate->execute(new InitiatePaymentDto(
            orderId: $event->orderId,
            method: $event->paymentMethod,
            changeFromRubles: $event->changeFromRubles,
            amountKopecks: $event->amountKopecks,
        ));
    }
}
