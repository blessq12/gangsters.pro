<?php

namespace App\Domain\Supporting\Warehouse\Entity;

/** Ингредиент склада: наименование, артикул (опционально) и единица измерения. */
final class Ingredient
{
    private function __construct(
        private ?int $id,
        private string $name,
        private ?string $sku,
        private string $unit,
    ) {}

    public static function create(string $name, ?string $sku, string $unit): self
    {
        return new self(
            id: null,
            name: self::requireText($name, 'Наименование'),
            sku: self::normalizeOptional($sku),
            unit: self::requireText($unit, 'Единица измерения'),
        );
    }

    public static function restore(int $id, string $name, ?string $sku, string $unit): self
    {
        return new self(id: $id, name: $name, sku: $sku, unit: $unit);
    }

    public function assignId(int $id): void
    {
        if ($this->id !== null) {
            throw new \LogicException('id уже назначен.');
        }

        if ($id < 1) {
            throw new \InvalidArgumentException('id должен быть положительным.');
        }

        $this->id = $id;
    }

    public function id(): int
    {
        if ($this->id === null) {
            throw new \LogicException('Ингредиент ещё не сохранён.');
        }

        return $this->id;
    }

    public function hasId(): bool
    {
        return $this->id !== null;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function sku(): ?string
    {
        return $this->sku;
    }

    public function unit(): string
    {
        return $this->unit;
    }

    private static function requireText(string $value, string $label): string
    {
        $value = trim($value);
        if ($value === '') {
            throw new \InvalidArgumentException($label . ' обязательно.');
        }

        return $value;
    }

    private static function normalizeOptional(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
