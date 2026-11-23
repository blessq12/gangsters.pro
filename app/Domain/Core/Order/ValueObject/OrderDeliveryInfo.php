<?php

namespace App\Domain\Core\Order\ValueObject;

use App\Shared\Enum\DeliveryMethod;

/**
 * Снимок способа получения: курьер или самовывоз.
 */
final readonly class OrderDeliveryInfo
{
    private function __construct(
        private DeliveryMethod $type,
        private ?OrderDeliveryAddress $address,
        private ?string $zoneId,
        private int $feeKopecks,
    ) {
        if ($this->feeKopecks < 0) {
            throw new \InvalidArgumentException('Стоимость доставки не может быть отрицательной.');
        }
    }

    public static function courier(
        OrderDeliveryAddress $address,
        string $zoneId,
        int $feeKopecks,
    ): self {
        if (trim($zoneId) === '') {
            throw new \InvalidArgumentException('Для курьерской доставки нужна зона.');
        }

        return new self(DeliveryMethod::Courier, $address, $zoneId, $feeKopecks);
    }

    public static function pickup(int $feeKopecks = 0): self
    {
        return new self(DeliveryMethod::Pickup, null, null, $feeKopecks);
    }

    /**
     * Снимок из хранилища. Старые заказы могут быть без зоны.
     */
    public static function restore(
        DeliveryMethod $type,
        ?OrderDeliveryAddress $address,
        ?string $zoneId,
        int $feeKopecks,
    ): self {
        $zoneId = $zoneId !== null && trim($zoneId) !== '' ? $zoneId : null;

        return match ($type) {
            DeliveryMethod::Pickup => new self(DeliveryMethod::Pickup, null, null, $feeKopecks),
            DeliveryMethod::Courier => new self(DeliveryMethod::Courier, $address, $zoneId, $feeKopecks),
        };
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
