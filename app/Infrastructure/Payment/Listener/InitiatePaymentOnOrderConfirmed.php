<?php

namespace App\Infrastructure\Payment\Listener;

use App\Application\Payment\Command\InitiatePaymentUseCase;
use App\Application\Payment\DTO\InitiatePaymentDto;
use App\Domain\Core\Order\Event\OrderConfirmed;
use App\Domain\Supporting\Payment\Repository\PaymentRepository;

/**
 * Order подтверждён → Payment заводит платёжное поручение.
 */
final class InitiatePaymentOnOrderConfirmed
{
    public function __construct(
        private readonly InitiatePaymentUseCase $initiate,
        private readonly PaymentRepository $payments,
    ) {}

    public function handle(OrderConfirmed $event): void
    {
        if ($this->payments->findByOrderId($event->orderId) !== null) {
            return;
        }

        $this->initiate->execute(new InitiatePaymentDto(
            orderId: $event->orderId,
            method: $event->paymentMethod,
            changeFromRubles: $event->changeFromRubles,
            amountKopecks: $event->amountKopecks,
        ));
    }
}
