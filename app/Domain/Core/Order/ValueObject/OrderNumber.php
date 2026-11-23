<?php

namespace App\Domain\Core\Order\ValueObject;

/**
 * Публичный номер заказа: ORD-XXXX-YY (id с нулями + год создания).
 */
final readonly class OrderNumber
{
    private const PREFIX = 'ORD';

    private const MIN_ID_WIDTH = 4;

    public static function format(int $id, \DateTimeInterface $createdAt): string
    {
        if ($id < 1) {
            throw new \InvalidArgumentException('Id заказа для номера должен быть >= 1.');
        }

        $year = (int) $createdAt->format('y');

        return sprintf(
            '%s-%s-%02d',
            self::PREFIX,
            str_pad((string) $id, self::MIN_ID_WIDTH, '0', STR_PAD_LEFT),
            $year,
        );
    }

    /**
     * @return array{id: int, year: int}|null
     */
    public static function tryParse(string $raw): ?array
    {
        $trimmed = trim($raw);
        if ($trimmed === '') {
            return null;
        }

        if (preg_match('/^ORD-0*([1-9]\d*)-(\d{2})$/i', $trimmed, $matches) !== 1) {
            return null;
        }

        return [
            'id' => (int) $matches[1],
            'year' => (int) $matches[2],
        ];
    }
}
