<?php

namespace App\Infrastructure\AggregatorIngress\Support;

use App\Domain\AggregatorIngress\Exception\IngressInvariantViolation;
use App\Domain\Client\ValueObject\PhoneNumber;
use DateTimeImmutable;

final class IngressAdapterSupport
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public static function requireString(array $payload, string $key, string $errorMessage): string
    {
        $value = trim((string) ($payload[$key] ?? ''));

        if ($value === '') {
            throw IngressInvariantViolation::invalidPayload($errorMessage);
        }

        return $value;
    }

    public static function parseDateTime(mixed $raw): DateTimeImmutable
    {
        if ($raw === null || $raw === '') {
            return new DateTimeImmutable();
        }

        $string = (string) $raw;
        $parsed = DateTimeImmutable::createFromFormat(DATE_ATOM, $string);

        return $parsed instanceof DateTimeImmutable ? $parsed : new DateTimeImmutable($string);
    }

    public static function rublesFromKopecks(int $kopecks): int
    {
        return (int) round($kopecks / 100);
    }

    public static function rublesFromMajorUnit(int|float|string $amount): int
    {
        return (int) round((float) $amount);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function nestedArray(array $payload, string $key): array
    {
        $value = $payload[$key] ?? null;

        return is_array($value) ? $value : [];
    }

    /**
     * Канонический телефон клиента: +7 (XXX) XXX-XX-XX.
     */
    public static function normalizeClientPhone(string $raw): string
    {
        try {
            return PhoneNumber::formatFromRaw($raw);
        } catch (\InvalidArgumentException) {
            throw IngressInvariantViolation::invalidPayload(
                'Телефон клиента должен содержать 10 цифр российского мобильного номера.',
            );
        }
    }
}
