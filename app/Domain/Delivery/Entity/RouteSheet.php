<?php

namespace App\Domain\Delivery\Entity;

final class RouteSheet
{
    /**
     * @param  list<RouteStop>  $stops
     */
    private function __construct(
        private ?int $id,
        private readonly string $status,
        private readonly array $stops,
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

        return new self(null, 'released', $stops);
    }

    /**
     * @param  list<RouteStop>  $stops
     */
    public static function restore(int $id, string $status, array $stops): self
    {
        return new self($id, $status, $stops);
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

    public function assignId(int $id): void
    {
        if ($this->id !== null) {
            throw new \LogicException('id уже назначен.');
        }

        $this->id = $id;
    }

    public function status(): string
    {
        return $this->status;
    }

    /**
     * @return list<RouteStop>
     */
    public function stops(): array
    {
        return $this->stops;
    }

    public function take(): self
    {
        if ($this->status !== 'released') {
            throw new \InvalidArgumentException('Этот лист нельзя взять в доставку.');
        }

        return new self($this->id, 'in_delivery', $this->stops);
    }

    public function settle(int $orderId, string $outcome, string $reason): self
    {
        if ($this->status !== 'in_delivery') {
            throw new \InvalidArgumentException('Статусы ставятся на листе, который курьер уже взял.');
        }

        if (! in_array($outcome, ['delivered', 'failed'], true)) {
            throw new \InvalidArgumentException('Недопустимый исход остановки.');
        }

        $reason = trim($reason);
        if ($outcome === 'failed' && $reason === '') {
            throw new \InvalidArgumentException('Нужна причина, почему заказ не выдан.');
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
                $outcome === 'failed' ? $reason : '',
            );
        }

        if (! $found) {
            throw new \InvalidArgumentException('Этого заказа нет в маршрутном листе.');
        }

        return new self($this->id, $this->status, $stops);
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

        return new self($this->id, 'completed', $this->stops);
    }
}
