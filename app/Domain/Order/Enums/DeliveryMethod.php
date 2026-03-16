<?php

namespace App\Domain\Order\Enums;

enum DeliveryMethod: string
{
    case Courier = 'courier';
    case Pickup = 'pickup';

    public function label(): string
    {
        return match ($this) {
            self::Courier => 'Курьер',
            self::Pickup => 'Самовывоз',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $out = [];
        foreach (self::cases() as $case) {
            $out[$case->value] = $case->label();
        }
        return $out;
    }
}
