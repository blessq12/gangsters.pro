<?php

namespace App\Shared\ValueObject;

/**
 * Деньги приложения: только целые рубли, валюта RUB.
 */
final readonly class Money
{
    public const CURRENCY = 'RUB';

    public function __construct(
        private int $amountRubles,
    ) {
        if ($this->amountRubles < 0) {
            throw new \InvalidArgumentException('Сумма в рублях не может быть отрицательной.');
        }
    }

    public static function rubles(int $amount): self
    {
        return new self($amount);
    }

    public static function zero(): self
    {
        return new self(0);
    }

    public function amountRubles(): int
    {
        return $this->amountRubles;
    }

    public function currency(): string
    {
        return self::CURRENCY;
    }

    public function isZero(): bool
    {
        return $this->amountRubles === 0;
    }

    public function equals(self $other): bool
    {
        return $this->amountRubles === $other->amountRubles;
    }

    public function add(self $other): self
    {
        return new self($this->amountRubles + $other->amountRubles);
    }

    public function subtract(self $other): self
    {
        $result = $this->amountRubles - $other->amountRubles;

        if ($result < 0) {
            throw new \InvalidArgumentException('Результат вычитания не может быть отрицательным.');
        }

        return new self($result);
    }
}
