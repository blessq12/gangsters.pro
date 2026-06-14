<?php

namespace App\Shared\Enum;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case CardCourier = 'card_courier';
    case CardOnline = 'card_online';

    /**
     * @return list<string>
     */
    public static function placementValues(): array
    {
        return array_map(
            static fn (self $method): string => $method->value,
            self::cases(),
        );
    }
}
