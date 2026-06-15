<?php

namespace App\Domain\AggregatorIngress\ValueObject;

use App\Shared\Enum\DeliveryMethod;
use App\Shared\Enum\PaymentMethod;
use DateTimeImmutable;

/**
 * Нормализованное ingress-обращение до резолва SKU (partner_sku в строках).
 */
final readonly class IngressMappedOrder
{
    /**
     * @param  list<IngressMappedLine>  $lines
     */
    public function __construct(
        public string $externalOrderId,
        public DateTimeImmutable $placedAt,
        public string $clientName,
        public string $clientPhone,
        public ?string $clientEmail,
        public DeliveryMethod $deliveryMethod,
        public ?IngressMappedAddress $deliveryAddress,
        public ?string $deliveryComment,
        public ?string $deliveryScheduledAt,
        public PaymentMethod $paymentMethod,
        public ?int $paymentChangeFromRubles,
        public array $lines,
    ) {}
}
