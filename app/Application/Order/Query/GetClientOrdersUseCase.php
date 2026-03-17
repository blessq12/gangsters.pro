<?php

namespace App\Application\Order\Query;

use App\Application\Order\OrderBaseUseCase;

final class GetClientOrdersUseCase extends OrderBaseUseCase
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function execute(?int $clientId = null): array
    {
        $id = $clientId ?? $this->authContext->currentClientId();

        $orders = $this->orders->findByClientId($id);

        return array_map(
            fn ($order) => $this->presenter->present($order),
            $orders,
        );
    }
}

