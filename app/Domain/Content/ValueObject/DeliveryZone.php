<?php

namespace App\Domain\Content\ValueObject;

/**
 * Зона доставки: полигон, тариф и флаг «отдалённый район».
 */
final class DeliveryZone
{
    /**
     * @param  array<string, mixed>  $geometry  GeoJSON Polygon / MultiPolygon
     */
    public function __construct(
        private readonly string $id,
        private readonly string $name,
        private readonly int $deliveryFeeKopecks,
        private readonly bool $isRemote,
        private readonly array $geometry,
    ) {}

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function deliveryFeeKopecks(): int
    {
        return $this->deliveryFeeKopecks;
    }

    public function isRemote(): bool
    {
        return $this->isRemote;
    }

    /**
     * @return array<string, mixed>
     */
    public function geometry(): array
    {
        return $this->geometry;
    }

    /**
     * @return array{
     *     id: string,
     *     name: string,
     *     delivery_fee_kopecks: int,
     *     is_remote: bool,
     *     geometry: array<string, mixed>
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'delivery_fee_kopecks' => $this->deliveryFeeKopecks,
            'is_remote' => $this->isRemote,
            'geometry' => $this->geometry,
        ];
    }
}
