<?php

namespace App\Application\Order\Command;

use App\Application\Order\DTO\UpdateAdminOrderDto;
use App\Application\Order\Presenter\AdminOrderPresenter;
use App\Application\Shared\DomainEventPublisher;
use App\Domain\Core\Order\Entity\Order;
use App\Domain\Core\Order\Enum\OrderLineType;
use App\Domain\Core\Order\Enum\OrderStatus;
use App\Domain\Core\Order\Repository\OrderAuditRepository;
use App\Domain\Core\Order\Repository\OrderRepository;
use App\Domain\Core\Order\ValueObject\OrderClientSnapshot;
use App\Domain\Core\Order\ValueObject\OrderDeliveryAddress;
use App\Domain\Core\Order\ValueObject\OrderDeliveryInfo;
use App\Domain\Core\Order\ValueObject\OrderLine;
use App\Shared\Enum\DeliveryMethod;
use App\Shared\Transaction\TransactionManager;
use DateTimeImmutable;

/**
 * Полная админ-правка полей заказа + аудит. Синк тикета — слушатель OrderLinesChanged.
 */
final class UpdateAdminOrderUseCase
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly OrderAuditRepository $audits,
        private readonly AdminOrderPresenter $presenter,
        private readonly DomainEventPublisher $events,
        private readonly TransactionManager $transaction,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(UpdateAdminOrderDto $dto): array
    {
        return $this->transaction->run(function () use ($dto): array {
            $order = $this->requireOrder($dto->orderId);
            if ($order->status() === OrderStatus::Delivered) {
                throw new \InvalidArgumentException('Выданный заказ нельзя редактировать.');
            }
            $changes = [];

            if ($dto->client !== null) {
                $before = [
                    'id' => $order->client()->clientId(),
                    'name' => $order->client()->name(),
                    'phone' => $order->client()->phone(),
                ];
                $name = trim((string) ($dto->client['name'] ?? $before['name']));
                $phone = trim((string) ($dto->client['phone'] ?? $before['phone']));
                $clientId = array_key_exists('id', $dto->client)
                    ? ($dto->client['id'] !== null ? (int) $dto->client['id'] : null)
                    : $before['id'];
                $order->replaceClient(new OrderClientSnapshot($clientId, $name, $phone));
                $after = ['id' => $clientId, 'name' => $name, 'phone' => $phone];
                if ($before !== $after) {
                    $changes[] = ['field' => 'client', 'old' => $before, 'new' => $after];
                }
            }

            if ($dto->delivery !== null) {
                $before = $this->deliverySnapshot($order);
                $delivery = $this->mapDelivery($dto->delivery, $order->delivery());
                $order->replaceDelivery($delivery);
                $after = $this->deliverySnapshot($order);
                if ($before !== $after) {
                    $changes[] = ['field' => 'delivery', 'old' => $before, 'new' => $after];
                }
            }

            if ($dto->paymentMethod !== null || $dto->changeFromRublesProvided) {
                $before = [
                    'payment_method' => $order->paymentMethod(),
                    'change_from_rubles' => $order->changeFromRubles(),
                ];
                $method = $dto->paymentMethod ?? (string) $order->paymentMethod();
                $change = $dto->changeFromRublesProvided
                    ? $dto->changeFromRubles
                    : $order->changeFromRubles();
                $order->replacePaymentTerms($method, $change);
                $after = [
                    'payment_method' => $order->paymentMethod(),
                    'change_from_rubles' => $order->changeFromRubles(),
                ];
                if ($before !== $after) {
                    $changes[] = ['field' => 'payment', 'old' => $before, 'new' => $after];
                }
            }

            if ($dto->lines !== null) {
                $before = $this->linesSnapshot($order);
                $lines = $this->mapLines($dto->lines);
                $order->replaceLines($lines);
                $after = $this->linesSnapshot($order);
                if ($before !== $after) {
                    $changes[] = ['field' => 'lines', 'old' => $before, 'new' => $after];
                }
            }

            if ($dto->scheduledAtProvided) {
                $before = $order->scheduledAt()?->format(DATE_ATOM);
                $parsed = $this->parseScheduledAt($dto->scheduledAt);
                $order->replaceScheduledAt($parsed);
                $after = $order->scheduledAt()?->format(DATE_ATOM);
                if ($before !== $after) {
                    $changes[] = ['field' => 'scheduled_at', 'old' => $before, 'new' => $after];
                }
            }

            if ($changes === []) {
                throw new \InvalidArgumentException('Нечего сохранять — нет изменений.');
            }

            $this->orders->save($order);
            $this->events->publish($order);
            $this->audits->add($order->id(), $dto->authorEmployeeId, $changes);

            return $this->presenter->present($this->requireOrder($order->id()));
        });
    }

    private function requireOrder(int $orderId): Order
    {
        return $this->orders->findById($orderId)
            ?? throw new \InvalidArgumentException('Заказ не найден.');
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function mapDelivery(array $payload, OrderDeliveryInfo $current): OrderDeliveryInfo
    {
        $methodRaw = (string) ($payload['method'] ?? $current->type()->value);
        $method = DeliveryMethod::from($methodRaw);

        if ($method === DeliveryMethod::Pickup) {
            return OrderDeliveryInfo::pickup((int) ($payload['fee_kopecks'] ?? $current->feeKopecks()));
        }

        $addressPayload = is_array($payload['address'] ?? null) ? $payload['address'] : [];
        $currentAddress = $current->address();
        $address = OrderDeliveryAddress::create(
            street: (string) ($addressPayload['street'] ?? $currentAddress?->street() ?? ''),
            house: (string) ($addressPayload['house'] ?? $currentAddress?->house() ?? ''),
            entrance: array_key_exists('entrance', $addressPayload)
                ? ($addressPayload['entrance'] !== null ? (string) $addressPayload['entrance'] : null)
                : $currentAddress?->entrance(),
            floor: array_key_exists('floor', $addressPayload)
                ? ($addressPayload['floor'] !== null ? (string) $addressPayload['floor'] : null)
                : $currentAddress?->floor(),
            apartment: array_key_exists('apartment', $addressPayload)
                ? ($addressPayload['apartment'] !== null ? (string) $addressPayload['apartment'] : null)
                : $currentAddress?->apartment(),
            comment: array_key_exists('comment', $addressPayload)
                ? ($addressPayload['comment'] !== null ? (string) $addressPayload['comment'] : null)
                : $currentAddress?->comment(),
            lat: array_key_exists('lat', $addressPayload)
                ? ($addressPayload['lat'] !== null ? (float) $addressPayload['lat'] : null)
                : $currentAddress?->lat(),
            lng: array_key_exists('lng', $addressPayload)
                ? ($addressPayload['lng'] !== null ? (float) $addressPayload['lng'] : null)
                : $currentAddress?->lng(),
        );

        $zoneId = (string) ($payload['zone_id'] ?? $current->zoneId() ?? '');
        $fee = (int) ($payload['fee_kopecks'] ?? $current->feeKopecks());

        return OrderDeliveryInfo::courier($address, $zoneId, $fee);
    }

    /**
     * @param  list<array{product_id: int, name: string, unit_price_kopecks: int, quantity: int, line_type: string}>  $payload
     * @return list<OrderLine>
     */
    private function mapLines(array $payload): array
    {
        $lines = [];
        foreach ($payload as $row) {
            $lines[] = new OrderLine(
                productId: (int) $row['product_id'],
                name: (string) $row['name'],
                unitPriceKopecks: (int) $row['unit_price_kopecks'],
                quantity: (int) $row['quantity'],
                lineType: OrderLineType::from((string) $row['line_type']),
            );
        }

        return $lines;
    }

    /**
     * @return array<string, mixed>
     */
    private function deliverySnapshot(Order $order): array
    {
        $delivery = $order->delivery();
        $address = $delivery->address();

        return [
            'method' => $delivery->type()->value,
            'zone_id' => $delivery->zoneId(),
            'fee_kopecks' => $delivery->feeKopecks(),
            'address' => $address === null ? null : [
                'street' => $address->street(),
                'house' => $address->house(),
                'entrance' => $address->entrance(),
                'floor' => $address->floor(),
                'apartment' => $address->apartment(),
                'comment' => $address->comment(),
                'lat' => $address->lat(),
                'lng' => $address->lng(),
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function linesSnapshot(Order $order): array
    {
        $rows = [];
        foreach ($order->lines() as $line) {
            $rows[] = [
                'product_id' => $line->productId(),
                'name' => $line->name(),
                'unit_price_kopecks' => $line->unitPriceKopecks(),
                'quantity' => $line->quantity(),
                'line_type' => $line->lineType()->value,
            ];
        }

        return $rows;
    }

    private function parseScheduledAt(?string $raw): ?DateTimeImmutable
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }

        try {
            return new DateTimeImmutable(trim($raw));
        } catch (\Exception) {
            throw new \InvalidArgumentException('Некорректный scheduled_at.');
        }
    }
}
