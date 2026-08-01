<?php

namespace App\Application\Order\useCases;

use App\Application\Order\DTO\GetOrderDto;
use App\Application\Order\Presenter\OrderPresenter;
use App\Domain\Order\Exception\OrderNotFoundException;
use App\Domain\Order\Repository\OrderRepository;
use Illuminate\Auth\AuthenticationException;

final class GetOrderUseCase
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly OrderPresenter $orderPresenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(GetOrderDto $input): array
    {
        $order = $this->orders->findById($input->orderId);

        if ($order === null) {
            throw OrderNotFoundException::forId($input->orderId);
        }

        $client = $order->client();

        if (
            ($client['kind'] ?? null) !== 'registered'
            || (int) ($client['client_id'] ?? 0) !== $input->clientId
        ) {
            throw new AuthenticationException();
        }

        return $this->orderPresenter->present($order);
    }
}
