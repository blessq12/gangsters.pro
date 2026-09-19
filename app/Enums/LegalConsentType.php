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

    public const LABELS = [
        self::OFFER => 'Публичная оферта',
        self::PDN => 'Согласие на обработку ПДн',
    ];

    public static function label(string $type): string
    {
        return self::LABELS[$type] ?? $type;
    }
}
