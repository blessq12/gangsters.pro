<?php

namespace App\Application\Order\DTO;

use App\Domain\Order\Enums\OrderSource;
use App\Domain\Order\ValueObjects\CustomerSnapshot;
use App\Domain\Order\ValueObjects\DeliveryInfo;
use App\Domain\Order\ValueObjects\PaymentInfo;

final class PlaceOrderInputDTO
{
    /**
     * @param  array<int, array{product_id: int, quantity: int, final_price_kopecks?: int|null}>  $items
     */
    public function __construct(
        public readonly OrderSource $source,
        public readonly ?int $clientId,
        public readonly CustomerSnapshot $customerSnapshot,
        public readonly array $items,
        public readonly DeliveryInfo $deliveryInfo,
        public readonly PaymentInfo $paymentInfo,
        public readonly int $deliveryFeeKopecks = 0,
        public readonly ?array $deliveryPricingSnapshot = null,
    ) {
    }
}
