<?php

namespace App\Application\Checkout\DTO;

/**
 * Вход сценария: зафиксировать контакт гостя и параметры доставки.
 */
final readonly class SetCheckoutClientAndDeliveryDto
{
    /**
     * @param  array<string, mixed>|null  $deliveryInfo
     * @param  array<string, mixed>|null  $guestContact
     */
    public function __construct(
        public ?array $deliveryInfo = null,
        public ?array $guestContact = null,
        public ?string $customerComment = null,
    ) {}
}
