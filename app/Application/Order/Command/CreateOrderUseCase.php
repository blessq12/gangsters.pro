<?php

namespace App\Application\Order\Command;

use App\Application\Order\Checkout\CheckoutQuote;
use App\Application\Order\DTO\CreateOrderDto;
use App\Application\Order\Presenter\OrderPresenter;
use App\Application\Shared\DomainEventPublisher;
use App\Domain\Core\Order\Entity\Order;
use App\Domain\Core\Order\Enum\OrderLineType;
use App\Domain\Core\Order\Repository\OrderRepository;
use App\Domain\Core\Order\ValueObject\OrderClientSnapshot;
use App\Domain\Core\Order\ValueObject\OrderDeliveryAddress;
use App\Domain\Core\Order\ValueObject\OrderDeliveryInfo;
use App\Domain\Core\Order\ValueObject\OrderLine;
use App\Shared\Enum\DeliveryMethod;
use App\Shared\Transaction\TransactionManager;
use DateTimeImmutable;

final class CreateOrderUseCase
{
    private const KOPECKS_IN_RUBLE = 100;

    public function __construct(
        private readonly OrderRepository $orders,
        private readonly OrderPresenter $presenter,
        private readonly DomainEventPublisher $events,
        private readonly TransactionManager $transaction,
        private readonly CheckoutQuote $quotes,
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

        $snapshot = $this->quotes->execute($dto->checkout);
        $cart = is_array($snapshot['cart'] ?? null) ? $snapshot['cart'] : [];
        $clientRaw = is_array($snapshot['client'] ?? null) ? $snapshot['client'] : [];
        $deliveryRaw = is_array($snapshot['delivery'] ?? null) ? $snapshot['delivery'] : [];
        $payment = is_array($snapshot['payment'] ?? null) ? $snapshot['payment'] : [];
        $benefits = is_array($snapshot['benefits'] ?? null) ? $snapshot['benefits'] : [];
        $totals = is_array($snapshot['totals'] ?? null)
            ? $snapshot['totals']
            : (is_array($cart['totals'] ?? null) ? $cart['totals'] : []);

        if (($deliveryRaw['method'] ?? '') === DeliveryMethod::Courier->value && ($deliveryRaw['in_zone'] ?? false) !== true) {
            throw new \InvalidArgumentException('Доставка по этому адресу невозможна.');
        }

        if (($benefits['gift_eligible'] ?? false) === true && ($benefits['gift_selected'] ?? false) !== true) {
            throw new \InvalidArgumentException('Выберите подарок из доступных кандидатов.');
        }

        $rawLines = $cart['lines'] ?? null;
        if (! is_array($rawLines) || $rawLines === []) {
            throw new \InvalidArgumentException('Корзина пуста.');
        }

        $this->assertTotalsConsistent($totals);

        $branchId = (int) ($snapshot['branch_id'] ?? $deliveryRaw['branch_id'] ?? 0);
        if ($branchId < 1) {
            throw new \InvalidArgumentException('В снимке quote нет branch_id.');
        }

        $order = Order::compose(
            checkoutId: $clientRequestId,
            client: $this->mapClient($clientRaw),
            delivery: $this->mapDelivery($deliveryRaw, $totals),
            lines: $this->mapLines($rawLines),
            createdAt: new DateTimeImmutable,
            branchId: $branchId,
            scheduledAt: $this->parseScheduledAt($deliveryRaw['scheduled_at'] ?? null),
        );

        $paymentMethod = (string) ($payment['method'] ?? '');
        $changeFromRubles = isset($payment['change_from_rubles'])
            ? (int) $payment['change_from_rubles']
            : null;

        return $this->transaction->run(function () use ($order, $paymentMethod, $changeFromRubles): array {
            $this->orders->save($order);
            $order->markPlaced($paymentMethod, $changeFromRubles);
            $this->orders->save($order);
            $this->events->publish($order);

            $saved = $this->orders->findById($order->id());
            if ($saved === null) {
                throw new \RuntimeException('Заказ не сохранился.');
            }

            return $this->presenter->present($saved);
        });
    }

    /**
     * @param  array<string, mixed>  $client
     */
    private function mapClient(array $client): OrderClientSnapshot
    {
        $clientId = isset($client['id']) ? (int) $client['id'] : (isset($client['client_id']) ? (int) $client['client_id'] : null);
        if ($clientId !== null && $clientId < 1) {
            $clientId = null;
        }

        return new OrderClientSnapshot(
            clientId: $clientId,
            name: (string) ($client['name'] ?? ''),
            phone: (string) ($client['phone'] ?? ''),
        );
    }

    /**
     * @param  array<string, mixed>  $delivery
     * @param  array<string, mixed>  $totals
     */
    private function mapDelivery(array $delivery, array $totals): OrderDeliveryInfo
    {
        $methodRaw = (string) ($delivery['method'] ?? $delivery['type'] ?? DeliveryMethod::Courier->value);
        $method = DeliveryMethod::tryFrom($methodRaw)
            ?? throw new \InvalidArgumentException('Некорректный способ получения.');

        $feeKopecks = isset($delivery['delivery_fee_kopecks'])
            ? (int) $delivery['delivery_fee_kopecks']
            : (isset($delivery['fee'])
                ? (int) $delivery['fee']
                : (isset($totals['delivery_fee'])
                    ? (int) $totals['delivery_fee']
                    : (int) ($delivery['delivery_fee_rubles'] ?? $totals['delivery_fee_rubles'] ?? 0) * self::KOPECKS_IN_RUBLE));

        if ($method === DeliveryMethod::Pickup) {
            return OrderDeliveryInfo::pickup(max(0, $feeKopecks));
        }

        $addressRaw = is_array($delivery['address'] ?? null) ? $delivery['address'] : $delivery;
        $zoneId = (string) ($delivery['zone_id'] ?? $delivery['zone']['id'] ?? '');

        return OrderDeliveryInfo::courier(
            address: new OrderDeliveryAddress(
                street: (string) ($addressRaw['street'] ?? ''),
                house: (string) ($addressRaw['house'] ?? ''),
                entrance: isset($addressRaw['entrance']) ? (string) $addressRaw['entrance'] : null,
                floor: isset($addressRaw['floor']) ? (string) $addressRaw['floor'] : null,
                apartment: isset($addressRaw['apartment']) ? (string) $addressRaw['apartment'] : null,
                comment: isset($addressRaw['comment']) ? (string) $addressRaw['comment'] : null,
            ),
            zoneId: $zoneId,
            feeKopecks: max(0, $feeKopecks),
        );
    }

    /**
     * @param  list<mixed>  $rawLines
     * @return list<OrderLine>
     */
    private function mapLines(array $rawLines): array
    {
        $lines = [];
        foreach ($rawLines as $raw) {
            if (! is_array($raw)) {
                continue;
            }

            $payload = is_array($raw['payload'] ?? null) ? $raw['payload'] : [];
            $kind = (string) ($payload['kind'] ?? $raw['line_type'] ?? 'product');
            $lineType = match ($kind) {
                'gift' => OrderLineType::Gift,
                'complement' => OrderLineType::Complement,
                'set' => OrderLineType::Set,
                'regular', 'product' => OrderLineType::Regular,
                default => OrderLineType::tryFrom($kind) ?? OrderLineType::Regular,
            };

            $unitPriceKopecks = isset($raw['unit_price_kopecks'])
                ? (int) $raw['unit_price_kopecks']
                : (int) ($raw['unit_price_rubles'] ?? 0) * self::KOPECKS_IN_RUBLE;

            if (in_array($lineType, [OrderLineType::Gift, OrderLineType::Complement], true)) {
                $unitPriceKopecks = 0;
            }

            $productId = (int) ($raw['product_id'] ?? 0);
            if ($productId < 1) {
                continue;
            }

            $name = (string) ($raw['product_name'] ?? $raw['name'] ?? '');
            $quantity = max(1, (int) ($raw['quantity'] ?? 1));

            $lines[] = new OrderLine(
                productId: $productId,
                name: $name,
                unitPriceKopecks: max(0, $unitPriceKopecks),
                quantity: $quantity,
                lineType: $lineType,
            );
        }

        if ($lines === []) {
            throw new \InvalidArgumentException('Корзина пуста.');
        }

        return $lines;
    }

    private function parseScheduledAt(mixed $raw): ?DateTimeImmutable
    {
        if ($raw === null || trim((string) $raw) === '') {
            return null;
        }

        try {
            return new DateTimeImmutable(trim((string) $raw));
        } catch (\Exception) {
            throw new \InvalidArgumentException('Некорректное желаемое время заказа.');
        }
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
