<?php

namespace App\Domain\Core\Order\ValueObject;

/**
 * Снимок клиента на момент оформления заказа.
 */
final readonly class OrderClientSnapshot
{
    public function __construct(
        private ?int $clientId,
        private string $name,
        private string $phone,
    ) {
        if ($this->clientId !== null && $this->clientId < 1) {
            throw new \InvalidArgumentException('clientId должен быть положительным.');
        }

        if (trim($this->name) === '') {
            throw new \InvalidArgumentException('Имя клиента обязательно.');
        }

        if (trim($this->phone) === '') {
            throw new \InvalidArgumentException('Телефон клиента обязателен.');
        }
    }

    public function clientId(): ?int
    {
        return $this->clientId;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function phone(): string
    {
        return $this->phone;
    }
}
