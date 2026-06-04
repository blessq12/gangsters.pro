<?php

namespace App\Application\Order\Command;

use App\Application\Order\Contracts\OrderPlacementContract;
use App\Application\Order\DTO\PlaceOrderInputDTO;
use App\Domain\Order\Entities\Order;

final class PlaceOrderWithChannelUseCase
{
    public function __construct(
        private readonly OrderPlacementContract $orderPlacement,
    ) {
    }

    public function execute(PlaceOrderInputDTO $input): Order
    {
        return $this->orderPlacement->place(
            clientId: $input->clientId,
            customerSnapshot: $input->customerSnapshot,
            items: $input->items,
            deliveryInfo: $input->deliveryInfo,
            paymentInfo: $input->paymentInfo,
            source: $input->source,
            deliveryFeeKopecks: $input->deliveryFeeKopecks,
            deliveryPricingSnapshot: $input->deliveryPricingSnapshot,
        );
    }
}
