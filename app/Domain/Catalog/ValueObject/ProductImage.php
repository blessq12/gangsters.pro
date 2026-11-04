<?php

namespace App\Domain\Catalog\ValueObject;

use InvalidArgumentException;

/**
 * Изображение товара в каталоге (storage-relative path + optional CSS focal point).
 */
final class ProductImage
{
    private readonly ?float $focusX;

    private readonly ?float $focusY;

    public function __construct(
        private readonly string $path,
        private readonly int $sortOrder,
        ?float $focusX = null,
        ?float $focusY = null,
    ) {
        $this->assertFocusPair($focusX, $focusY);
        $this->focusX = $focusX;
        $this->focusY = $focusY;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function sortOrder(): int
    {
        return $this->sortOrder;
    }

    /**
     * Разрешённый X для object-position (0..100). Null в storage → 50.
     */
    public function focusX(): float
    {
        return $this->focusX ?? 50.0;
    }

    /**
     * Разрешённый Y для object-position (0..100). Null в storage → 50.
     */
    public function focusY(): float
    {
        return $this->focusY ?? 50.0;
    }

    public function hasDefaultFocus(): bool
    {
        return $this->focusX === null && $this->focusY === null;
    }

    /**
     * Значение для CSS object-position, например "35.5% 60%".
     */
    public function objectPositionCss(): string
    {
        return $this->formatPercent($this->focusX()).' '.$this->formatPercent($this->focusY());
    }

    /**
     * @return array{x: float, y: float}
     */
    public function focusPayload(): array
    {
        return [
            'x' => $this->focusX(),
            'y' => $this->focusY(),
        ];
    }

    private function assertFocusPair(?float $focusX, ?float $focusY): void
    {
        if ($focusX === null && $focusY === null) {
            return;
        }

        if ($focusX === null || $focusY === null) {
            throw new InvalidArgumentException('focus_x и focus_y задаются только парой.');
        }

        if (! $this->isValidPercent($focusX) || ! $this->isValidPercent($focusY)) {
            throw new InvalidArgumentException('focus_x и focus_y должны быть в диапазоне 0..100.');
        }
    }

    private function isValidPercent(float $value): bool
    {
        return $value >= 0.0 && $value <= 100.0;
    }

    private function formatPercent(float $value): string
    {
        $formatted = rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');

        return $formatted.'%';
    }
}
