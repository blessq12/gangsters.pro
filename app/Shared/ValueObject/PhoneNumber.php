<?php

namespace App\Shared\ValueObject;

/**
 * Российский телефон. Канон: +7 (XXX) XXX-XX-XX.
 */
final readonly class PhoneNumber
{
    public const PATTERN = '+7 (###) ###-##-##';

    private function __construct(
        private string $digits,
    ) {}

    public static function fromRaw(?string $raw): self
    {
        $digits = self::normalizeDigits($raw);

        if ($digits === '') {
            throw new \InvalidArgumentException('Телефон обязателен.');
        }

        if (strlen($digits) !== 10) {
            throw new \InvalidArgumentException('Телефон должен содержать 10 цифр.');
        }

        return new self($digits);
    }

    public static function tryFromRaw(?string $raw): ?self
    {
        $digits = self::normalizeDigits($raw);

        if (strlen($digits) !== 10) {
            return null;
        }

        return new self($digits);
    }

    /**
     * Нормализация в 10 цифр абонента (без кода страны).
     */
    public static function normalizeDigits(?string $raw): string
    {
        $digits = preg_replace('/\D+/', '', (string) $raw) ?? '';

        if (
            strlen($digits) === 11
            && ($digits[0] === '7' || $digits[0] === '8')
        ) {
            $digits = substr($digits, 1);
        }

        while (
            strlen($digits) > 0
            && strlen($digits) < 11
            && ($digits[0] === '7' || $digits[0] === '8')
        ) {
            $digits = substr($digits, 1);
        }

        return substr($digits, 0, 10);
    }

    /**
     * Всегда возвращает +7 (XXX) XXX-XX-XX либо бросает.
     */
    public static function formatFromRaw(?string $raw): string
    {
        return self::fromRaw($raw)->formatted();
    }

    /**
     * +7 (XXX) XXX-XX-XX или null, если номер неполный.
     */
    public static function tryFormatFromRaw(?string $raw): ?string
    {
        return self::tryFromRaw($raw)?->formatted();
    }

    public static function formatDigits(string $digits): string
    {
        if (strlen($digits) !== 10 || ! ctype_digit($digits)) {
            throw new \InvalidArgumentException('Для форматирования нужны ровно 10 цифр.');
        }

        return sprintf(
            '+7 (%s) %s-%s-%s',
            substr($digits, 0, 3),
            substr($digits, 3, 3),
            substr($digits, 6, 2),
            substr($digits, 8, 2),
        );
    }

    /** 10 цифр абонента без кода страны. */
    public function digits(): string
    {
        return $this->digits;
    }

    /** Всегда +7 (XXX) XXX-XX-XX. */
    public function formatted(): string
    {
        return self::formatDigits($this->digits);
    }

    public function equals(self $other): bool
    {
        return $this->digits === $other->digits;
    }

    public function __toString(): string
    {
        return $this->formatted();
    }
}
