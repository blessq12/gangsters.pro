<?php

namespace App\Domain\Promotion\Enum;

enum DeliveryFeeMode: string
{
    case BaseTariff = 'base_tariff';
    case Free = 'free';
    case BasePlusSurcharge = 'base_plus_surcharge';
    case OutsideZoneSurchargeOnly = 'outside_zone_surcharge_only';
}
