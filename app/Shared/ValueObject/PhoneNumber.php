<?php

namespace App\Shared\ValueObject;

final readonly class PhoneNumber
{
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

    public static function formatFromRaw(?string $raw): string
    {
        return self::fromRaw($raw)->formatted();
    }

    public static function tryFormatFromRaw(?string $raw): ?string
    {
        $digits = self::normalizeDigits($raw);

        if (strlen($digits) !== 10) {
            return null;
        }

        return self::formatDigits($digits);
    }

    public static function formatDigits(string $digits): string
    {
        return sprintf(
            '+7 (%s) %s-%s-%s',
            substr($digits, 0, 3),
            substr($digits, 3, 3),
            substr($digits, 6, 2),
            substr($digits, 8, 2),
        );
    }

    public function digits(): string
    {
        return $this->digits;
    }

    public function formatted(): string
    {
        return self::formatDigits($this->digits);
    }
}
