<?php

namespace App\Enums;

final class LegalConsentType
{
    public const OFFER = 'offer';
    public const PDN = 'pdn';

    public const ALL = [
        self::OFFER,
        self::PDN,
    ];
}
