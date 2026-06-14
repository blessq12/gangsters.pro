<?php

namespace App\Domain\Client\ValueObject;

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

    public function digits(): string
    {
        return $this->digits;
    }
}
