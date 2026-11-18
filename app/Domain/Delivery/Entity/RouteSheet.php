<?php

namespace App\Domain\Delivery\Entity;

use App\Domain\Delivery\Event\OrderAssignedToRoute;
use App\Domain\Delivery\Event\RouteSheetTaken;
use App\Domain\Delivery\Event\RouteStopSettled;

final class RouteSheet
{
    /**
     * @param  list<RouteStop>  $stops
     * @param  list<object>  $domainEvents
     */
    private function __construct(
        private ?int $id,
        private readonly string $status,
        private readonly array $stops,
        private readonly ?int $courierId,
        private array $domainEvents = [],
    ) {}

    /**
     * @param  list<int>  $orderIds
     */
    public static function release(array $orderIds): self
    {
        if ($orderIds === []) {
            throw new \InvalidArgumentException('В маршрутном листе нет заказов.');
        }

        $stops = [];
        foreach (array_values($orderIds) as $index => $orderId) {
            $stops[] = new RouteStop($orderId, $index + 1, 'pending', '');
        }

        return new self(null, 'released', $stops, null);
    }

    /**
     * @param  list<RouteStop>  $stops
     */
    public static function restore(int $id, string $status, array $stops, ?int $courierId = null): self
    {
        return new self($id, $status, $stops, $courierId);
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
     * Факт «заказ назначен на маршрут» возникает, когда выпущенный лист получает идентичность.
     */
    public function assignId(int $id): void
    {
        if ($this->id !== null) {
            throw new \LogicException('id уже назначен.');
        }

        $this->id = $id;

        if ($this->status === 'released') {
            foreach ($this->stops as $stop) {
                $this->domainEvents[] = new OrderAssignedToRoute(
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
            'in_delivery',
            $this->stops,
            $courierId,
            [...$this->domainEvents, new RouteSheetTaken(routeSheetId: $this->id(), orderIds: $orderIds)],
        );
    }

    public function settle(int $orderId, string $outcome, string $reason): self
    {
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
            $stops[] = new RouteStop(
                $stop->orderId(),
                $stop->position(),
                $outcome,
                $outcome === RouteStopSettled::OUTCOME_WRITTEN_OFF ? $reason : '',
            );
        }

        if (! $found) {
            throw new \InvalidArgumentException('Этого заказа нет в маршрутном листе.');
        }

        return new self(
            $this->id,
            $this->status,
            $stops,
            $this->courierId,
            [...$this->domainEvents, new RouteStopSettled(
                routeSheetId: $this->id(),
                orderId: $orderId,
                outcome: $outcome,
                reason: $outcome === RouteStopSettled::OUTCOME_WRITTEN_OFF ? $reason : null,
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
            $stops[] = new RouteStop(
                $stop->orderId(),
                $stop->position(),
                RouteStop::OUTCOME_CANCELLED,
                '',
            );
        }

        if (! $found) {
            throw new \InvalidArgumentException('Этого заказа нет среди ожидающих остановок листа.');
        }

        return new self($this->id, $this->status, $stops, $this->courierId, $this->domainEvents);
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

        return new self($this->id, 'completed', $this->stops, $this->courierId, $this->domainEvents);
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
