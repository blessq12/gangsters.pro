<?php

namespace App\Domain\Core\Order\ValueObject;

use App\Shared\Enum\DeliveryMethod;

/**
 * Снимок способа получения: курьер или самовывоз.
 */
final readonly class OrderDeliveryInfo
{
    public function __construct(
        private DeliveryMethod $type,
        private ?OrderDeliveryAddress $address,
        private ?string $zoneId,
        private int $feeKopecks,
    ) {
        if ($this->feeKopecks < 0) {
            throw new \InvalidArgumentException('Стоимость доставки не может быть отрицательной.');
        }

        if ($this->type === DeliveryMethod::Courier) {
            if ($this->address === null) {
                throw new \InvalidArgumentException('Для курьерской доставки нужен адрес.');
            }
            if ($this->zoneId === null || trim($this->zoneId) === '') {
                throw new \InvalidArgumentException('Для курьерской доставки нужна зона.');
            }
        }

        if ($this->type === DeliveryMethod::Pickup && $this->address !== null) {
            throw new \InvalidArgumentException('У самовывоза не может быть адреса доставки.');
        }
    }

    public static function courier(
        OrderDeliveryAddress $address,
        string $zoneId,
        int $feeKopecks,
    ): self {
        return new self(DeliveryMethod::Courier, $address, $zoneId, $feeKopecks);
    }

    public static function pickup(int $feeKopecks = 0): self
    {
        return new self(DeliveryMethod::Pickup, null, null, $feeKopecks);
    }

    public function type(): DeliveryMethod
    {
        return $this->type;
    }

    public function address(): ?OrderDeliveryAddress
    {
        return $this->address;
    }

    public function zoneId(): ?string
    {
        return $this->zoneId;
    }

    public function feeKopecks(): int
    {
        return $this->feeKopecks;
    }

    public function withFee(int $feeKopecks): self
    {
        return new self($this->type, $this->address, $this->zoneId, $feeKopecks);
    }

    public function isCourier(): bool
    {
        return $this->type === DeliveryMethod::Courier;
    }

    public function isPickup(): bool
    {
        return $this->type === DeliveryMethod::Pickup;
    }
}
