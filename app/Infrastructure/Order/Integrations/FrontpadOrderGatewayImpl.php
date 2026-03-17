<?php

namespace App\Infrastructure\Order\Integrations;

use App\Domain\Order\Entities\Order;
use App\Domain\Order\Integrations\FrontpadOrderGateway;

final class FrontpadOrderGatewayImpl implements FrontpadOrderGateway
{
    public function pushOrder(Order $order): void
    {
        // Реализация интеграции с Frontpad будет добавлена позже.
    }
}

