<?php

namespace App\Application\Payment\Command;

use App\Application\Payment\Presenter\PaymentPresenter;
use App\Domain\Payment\Repository\PaymentRepository;

final class RefundPaymentUseCase
{
    public function __construct(
        private readonly PaymentRepository $payments,
        private readonly PaymentPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(int $paymentId): array
    {
        $payment = $this->payments->findById($paymentId);
        if ($payment === null) {
            throw new \InvalidArgumentException('Платёж не найден.');
        }

        try {
            $payment->refund();
        } catch (\LogicException $exception) {
            throw new \InvalidArgumentException($exception->getMessage(), 0, $exception);
        }

        $this->payments->save($payment);

        foreach ($payment->pullDomainEvents() as $event) {
            event($event);
        }

        return $this->presenter->present($payment);
    }
}
