<?php

namespace App\Application\Checkout\DTO;

/**
 * Вход сценария: зафиксировать способ оплаты.
 */
final readonly class SetCheckoutPaymentDto
{
    /**
     * @param  array<string, mixed>|null  $paymentInfo
     */
    public function __construct(
        public ?array $paymentInfo = null,
    ) {}
}
