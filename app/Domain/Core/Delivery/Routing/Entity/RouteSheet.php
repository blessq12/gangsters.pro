<?php

namespace App\Domain\Core\Delivery\Routing\Entity;

use App\Domain\Core\Delivery\Event\OrderAddedToRoute;
use App\Domain\Core\Delivery\Event\RouteSheetCreated;
use App\Domain\Core\Delivery\Event\RouteSheetTaken;
use App\Domain\Core\Delivery\Event\RouteStopSettled;

final class RouteSheet
{
    /**
     * @param  list<RouteStop>  $stops
     * @param  list<object>  $domainEvents
     */
    private function __construct(
        private ?int $id,
        private readonly int $branchId,
        private readonly string $status,
        private readonly array $stops,
        private readonly ?int $courierId,
        private array $domainEvents = [],
    ) {}

    /**
     * Выпуск листа филиала.
     *
     * @param  list<int>  $orderIds
     */
    public static function release(int $branchId, array $orderIds): self
    {
        if ($branchId < 1) {
            throw new \InvalidArgumentException('branchId маршрутного листа должен быть положительным.');
        }
        if ($orderIds === []) {
            throw new \InvalidArgumentException('В маршрутном листе нет заказов.');
        }

        $stops = [];
        foreach (array_values($orderIds) as $index => $orderId) {
            $orderId = (int) $orderId;
            if ($orderId < 1) {
                throw new \InvalidArgumentException('orderId в остановке должен быть положительным.');
            }

            $stops[] = new RouteStop(
                orderId: $orderId,
                position: $index + 1,
                outcome: 'pending',
                reason: '',
            );
        }

        return new self(null, $branchId, 'released', $stops, null);
    }

    /**
     * @param  list<RouteStop>  $stops
     */
    public static function restore(int $id, int $branchId, string $status, array $stops, ?int $courierId = null): self
    {
        return new self($id, $branchId, $status, $stops, $courierId);
    }

    public function branchId(): int
    {
        return $this->branchId;
    }

    public function id(): int
    {
        if ($this->id === null) {
            throw new \LogicException('Маршрутный лист ещё не сохранён.');
        }

        return $this->id;
    }

    public function hasId(): bool
    {
        return $this->id !== null;
    }

    /**
     * Факт выпуска листа и добавления заказов в маршрут — когда лист получает идентичность.
     */
    public function assignId(int $id): void
    {
        if ($this->id !== null) {
            throw new \LogicException('id уже назначен.');
        }

        $this->id = $id;

        if ($this->status === 'released') {
            $this->domainEvents[] = new RouteSheetCreated(routeSheetId: $id);

            foreach ($this->stops as $stop) {
                $this->domainEvents[] = new OrderAddedToRoute(
                    orderId: $stop->orderId(),
                    routeSheetId: $id,
                );
            }
        }
    }

    public function status(): string
    {
        return $this->status;
    }

    public function courierId(): ?int
    {
        return $this->courierId;
    }

    /**
     * @return list<RouteStop>
     */
    public function stops(): array
    {
        return $this->stops;
    }

    public function take(int $courierId): self
    {
        if ($this->status !== 'released') {
            throw new \InvalidArgumentException('Этот лист нельзя взять в доставку.');
        }

        if ($courierId < 1) {
            throw new \InvalidArgumentException('courierId должен быть положительным.');
        }

        $orderIds = array_map(
            static fn (RouteStop $stop): int => $stop->orderId(),
            $this->stops,
        );

        return new self(
            $this->id,
            $this->branchId,
            'in_delivery',
            $this->stops,
            $courierId,
            [...$this->domainEvents, new RouteSheetTaken(routeSheetId: $this->id(), orderIds: $orderIds)],
        );
    }

    public function settle(
        int $orderId,
        string $outcome,
        string $reason,
        ?string $paymentMethod = null,
        ?int $authorEmployeeId = null,
    ): self {
        if ($this->status !== 'in_delivery') {
            throw new \InvalidArgumentException('Статусы ставятся на листе, который курьер уже взял.');
        }

        if (! in_array($outcome, [RouteStopSettled::OUTCOME_DELIVERED, RouteStopSettled::OUTCOME_WRITTEN_OFF], true)) {
            throw new \InvalidArgumentException('Недопустимый исход остановки.');
        }

        $reason = trim($reason);
        if ($outcome === RouteStopSettled::OUTCOME_WRITTEN_OFF && $reason === '') {
            throw new \InvalidArgumentException('Нужна причина списания заказа.');
        }

        $paymentMethod = $paymentMethod !== null ? trim($paymentMethod) : null;
        if ($outcome === RouteStopSettled::OUTCOME_DELIVERED && ($paymentMethod === null || $paymentMethod === '')) {
            throw new \InvalidArgumentException('Укажи способ оплаты при выдаче.');
        }

        $found = false;
        $stops = [];
        foreach ($this->stops as $stop) {
            if ($stop->orderId() !== $orderId) {
                $stops[] = $stop;

                continue;
            }
            if ($stop->outcome() !== 'pending') {
                throw new \InvalidArgumentException('По этой остановке статус уже стоит.');
            }
            $found = true;
            $stops[] = $stop->withOutcome(
                $outcome,
                $outcome === RouteStopSettled::OUTCOME_WRITTEN_OFF ? $reason : '',
            );
        }

        if (! $found) {
            throw new \InvalidArgumentException('Этого заказа нет в маршрутном листе.');
        }

        return new self(
            $this->id,
            $this->branchId,
            $this->status,
            $stops,
            $this->courierId,
            [...$this->domainEvents, new RouteStopSettled(
                routeSheetId: $this->id(),
                orderId: $orderId,
                outcome: $outcome,
                reason: $outcome === RouteStopSettled::OUTCOME_WRITTEN_OFF ? $reason : null,
                paymentMethod: $outcome === RouteStopSettled::OUTCOME_DELIVERED ? $paymentMethod : null,
                authorEmployeeId: $authorEmployeeId,
            )],
        );
    }

    /**
     * Заказ отменён: остановка помечается отменённой, лист остаётся.
     */
    public function cancelStop(int $orderId): self
    {
        if (! in_array($this->status, ['released', 'in_delivery'], true)) {
            throw new \InvalidArgumentException('Лист уже закрыт.');
        }

        $found = false;
        $stops = [];
        foreach ($this->stops as $stop) {
            if ($stop->orderId() !== $orderId || $stop->outcome() !== 'pending') {
                $stops[] = $stop;

                continue;
            }
            $found = true;
            $stops[] = $stop->withOutcome(RouteStop::OUTCOME_CANCELLED);
        }

        if (! $found) {
            throw new \InvalidArgumentException('Этого заказа нет среди ожидающих остановок листа.');
        }

        return new self($this->id, $this->branchId, $this->status, $stops, $this->courierId, $this->domainEvents);
    }

    public function complete(): self
    {
        if ($this->status !== 'in_delivery') {
            throw new \InvalidArgumentException('Завершить можно только лист в доставке.');
        }

        foreach ($this->stops as $stop) {
            if ($stop->outcome() === 'pending') {
                throw new \InvalidArgumentException('Сначала проставьте статус по каждой остановке.');
            }
        }

        return new self($this->id, $this->branchId, 'completed', $this->stops, $this->courierId, $this->domainEvents);
    }

    /**
     * @return list<object>
     */
    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }
}
