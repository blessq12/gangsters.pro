<?php

namespace App\Domain\Client\VO;

use InvalidArgumentException;

final class PhoneNumber
{
    public function __construct(
        private string $value,
    ) {
        $normalized = preg_replace('/\D+/', '', $this->value ?? '');

        // Нормализуем российский номер: отрезаем ведущую 7/8, если длина 11.
        if ($normalized !== null && strlen($normalized) === 11 && in_array($normalized[0], ['7', '8'], true)) {
            $normalized = substr($normalized, 1);
        }

        if ($normalized === null || $normalized === '' || strlen($normalized) < 6) {
            throw new InvalidArgumentException('Invalid phone number');
        }

        $this->value = $normalized;
    }

    public function value(): string
    {
        return $this->value;
    }

    /**
     * Возвращает номер в формате РФ: +7 (XXX) XXX-XX-XX, если удалось
     * корректно распарсить 10 цифр. В противном случае — возвращает
     * как есть (без форматирования), чтобы не ломать экзотические номера.
     */
    public function formatted(): string
    {
        $digits = preg_replace('/\D+/', '', $this->value ?? '');

        if ($digits === null) {
            return $this->value;
        }

        // После конструктора здесь обычно уже 10 цифр, но на всякий случай
        // ещё раз приводим к 10-значному формату РФ.
        if (strlen($digits) === 11 && in_array($digits[0], ['7', '8'], true)) {
            $digits = substr($digits, 1);
        }

        if (strlen($digits) !== 10) {
            return $this->value;
        }

        $code = substr($digits, 0, 3);
        $part1 = substr($digits, 3, 3);
        $part2 = substr($digits, 6, 2);
        $part3 = substr($digits, 8, 2);

        return sprintf('+7 (%s) %s-%s-%s', $code, $part1, $part2, $part3);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

