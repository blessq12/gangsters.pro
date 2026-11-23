<?php

namespace App\Application\Payment\Command;

use App\Application\Payment\DTO\InitiatePaymentDto;
use App\Domain\Supporting\Payment\Entity\Payment;
use App\Domain\Supporting\Payment\Repository\PaymentRepository;
use App\Shared\Enum\PaymentMethod;

/**
 * Заводит ожидающий платёж по заказу и публикует PaymentInitiated.
 */
final class InitiatePaymentUseCase
{
    public function __construct(
        private readonly PaymentRepository $payments,
    ) {}

    public function execute(InitiatePaymentDto $dto): int
    {
        if ($this->payments->findByOrderId($dto->orderId) !== null) {
            throw new \InvalidArgumentException('Платёж по этому заказу уже заведён.');
        }

        $method = PaymentMethod::tryFrom(trim($dto->method));
        if ($method === null) {
            throw new \InvalidArgumentException('Некорректный способ оплаты.');
        }

        $payment = Payment::initiate(
            orderId: $dto->orderId,
            method: $method,
            changeFromRubles: $dto->changeFromRubles,
            amountKopecks: $dto->amountKopecks,
        );
        $this->payments->add($payment);

        foreach ($payment->pullDomainEvents() as $event) {
            event($event);
        }

        return $payment->id();
    }
}
