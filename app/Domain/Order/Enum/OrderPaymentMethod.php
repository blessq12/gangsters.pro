<?php

namespace App\Domain\Order\Enum;

enum OrderPaymentMethod: string
{
    case Cash = 'cash';
    case CardCourier = 'card_courier';
    case CardOnline = 'card_online';
}
