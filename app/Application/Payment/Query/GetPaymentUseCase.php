<?php

namespace App\Application\Payment\Query;

use App\Application\Payment\Presenter\PaymentPresenter;
use App\Domain\Payment\Repository\PaymentRepository;

final class GetPaymentUseCase
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

        return $this->presenter->present($payment);
    }
}
