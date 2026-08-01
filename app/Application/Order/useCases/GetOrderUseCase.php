<?php

namespace App\Application\Order\useCases;

use Illuminate\Auth\AuthenticationException;
use App\Application\Order\DTO\GetOrderDto;
use App\Application\Order\Presenter\OrderPresenter;
use App\Domain\Order\Exception\OrderNotFoundException;
use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Order\ValueObject\OrderId;
use App\Shared\Enum\ClientKind;

/**
 * Сценарий: детали заказа авторизованного клиента.
 */
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
        $order = $this->orders->findById(OrderId::fromInt($input->orderId));

        if ($order === null) {
            throw OrderNotFoundException::forId($input->orderId);
        }

        $client = $order->client();

        if (
            $client->kind() !== ClientKind::Registered
            || $client->clientId() !== $input->clientId
        ) {
            throw new AuthenticationException();
        }

        return $this->orderPresenter->present($order);
    }
}
