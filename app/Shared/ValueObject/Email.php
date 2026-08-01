<?php

namespace App\Shared\ValueObject;

/**
 * Email приложения.
 */
final readonly class Email
{
    private function __construct(
        private string $value,
    ) {}

    public static function fromRaw(?string $raw): self
    {
        $value = self::normalize($raw);

        if ($value === '') {
            throw new \InvalidArgumentException('Email обязателен.');
        }

        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            throw new \InvalidArgumentException('Некорректный email.');
        }

        return new self($value);
    }

    public static function tryFromRaw(?string $raw): ?self
    {
        $value = self::normalize($raw);

        if ($value === '' || filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            return null;
        }

        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    private static function normalize(?string $raw): string
    {
        return mb_strtolower(trim((string) $raw));
    }
}
