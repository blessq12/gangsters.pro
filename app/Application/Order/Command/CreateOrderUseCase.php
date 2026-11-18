<?php

namespace App\Application\Order\Command;

use App\Application\Order\DTO\CreateOrderDto;
use App\Application\Order\Presenter\OrderPresenter;
use App\Domain\Order\Entity\Order;
use App\Domain\Order\Repository\OrderRepository;
use DateTimeImmutable;

final class CreateOrderUseCase
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly OrderPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(CreateOrderDto $dto): array
    {
        $clientRequestId = trim($dto->clientRequestId);
        if ($clientRequestId === '') {
            throw new \InvalidArgumentException('client_request_id обязателен.');
        }

        if ($this->orders->findByClientRequestId($clientRequestId) !== null) {
            throw new \InvalidArgumentException('Заказ с таким client_request_id уже существует.');
        }

        $snapshot = $dto->quoteSnapshot;
        $cart = is_array($snapshot['cart'] ?? null) ? $snapshot['cart'] : [];
        $client = is_array($snapshot['client'] ?? null) ? $snapshot['client'] : [];
        $delivery = is_array($snapshot['delivery'] ?? null) ? $snapshot['delivery'] : [];
        $payment = is_array($snapshot['payment'] ?? null) ? $snapshot['payment'] : [];
        $totals = is_array($snapshot['totals'] ?? null)
            ? $snapshot['totals']
            : (is_array($cart['totals'] ?? null) ? $cart['totals'] : []);

        $lines = $cart['lines'] ?? null;
        if (! is_array($lines) || $lines === []) {
            throw new \InvalidArgumentException('Корзина пуста.');
        }

        $this->assertTotalsConsistent($totals);

        if (! isset($cart['totals']) && $totals !== []) {
            $cart['totals'] = $totals;
        }

        $order = Order::fromCheckoutSnapshot(
            clientRequestId: $clientRequestId,
            cart: $cart,
            client: $client,
            delivery: $delivery,
            payment: $payment,
            createdAt: new DateTimeImmutable(),
        );

        $this->orders->save($order);

        return $this->presenter->present($order);
    }

    /**
     * @param  array<string, mixed>  $totals
     */
    private function assertTotalsConsistent(array $totals): void
    {
        if ($totals === []) {
            throw new \InvalidArgumentException('В снимке нет totals.');
        }

        $items = (int) ($totals['items_total'] ?? $totals['items_total_rubles'] ?? -1);
        $deliveryFee = (int) ($totals['delivery_fee'] ?? $totals['delivery_fee_rubles'] ?? -1);
        $grand = (int) ($totals['grand_total'] ?? $totals['grand_total_rubles'] ?? -1);

        if ($items < 0 || $deliveryFee < 0 || $grand < 0) {
            throw new \InvalidArgumentException('Totals некорректны.');
        }

        if (isset($totals['items_total'], $totals['delivery_fee'], $totals['grand_total'])) {
            $expected = (int) $totals['items_total'] + (int) $totals['delivery_fee'];
            if ($expected !== (int) $totals['grand_total']) {
                throw new \InvalidArgumentException('Totals не сходятся.');
            }

            return;
        }

        $expectedRubles = (int) ($totals['items_total_rubles'] ?? 0)
            + (int) ($totals['delivery_fee_rubles'] ?? 0);
        if ($expectedRubles !== (int) ($totals['grand_total_rubles'] ?? -1)) {
            throw new \InvalidArgumentException('Totals не сходятся.');
        }
    }
}
