<?php

namespace App\Domain\Order\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Card = 'card';

    /** Legacy: только чтение исторических заказов, не для placement. */
    case Transfer = 'transfer';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Наличные',
            self::Card => 'Банковская карта',
            self::Transfer => 'Перевод',
        };
    }

    /**
     * Способы оплаты при оформлении (чекаут, POST /api/order).
     *
     * @return list<self>
     */
    public static function forPlacement(): array
    {
        return [
            self::Cash,
            self::Card,
        ];
    }

    /**
     * @return list<string>
     */
    public static function placementValues(): array
    {
        return array_map(
            static fn (self $method): string => $method->value,
            self::forPlacement(),
        );
    }

    /**
     * @return array<string, string>
     */
    public static function placementOptions(): array
    {
        $out = [];
        foreach (self::forPlacement() as $case) {
            $out[$case->value] = $case->label();
        }

        return $out;
    }

    /**
     * Все методы (включая legacy transfer) — фильтры админки, отчёты.
     *
     * @return array<string, string>
     */
    public static function allOptions(): array
    {
        $out = [];
        foreach (self::cases() as $case) {
            $out[$case->value] = $case->label();
        }

        return $out;
    }

    /**
     * @return array<string, string>
     *
     * @deprecated Use {@see allOptions()} or {@see placementOptions()}
     */
    public static function options(): array
    {
        return self::allOptions();
    }
}
