<?php

namespace App\Enums;

final class LegalDocumentType
{
    public const PRIVACY = 'privacy';
    public const OFFER = 'offer';
    public const TERMS = 'terms';
    public const PDN_CONSENT = 'pdn_consent';
    public const COOKIES = 'cookies';
    public const SELLER_INFO = 'seller_info';

    public const ALL = [
        self::PRIVACY,
        self::OFFER,
        self::TERMS,
        self::PDN_CONSENT,
        self::COOKIES,
        self::SELLER_INFO,
    ];

    public const LABELS = [
        self::PRIVACY => 'Политика конфиденциальности',
        self::OFFER => 'Публичная оферта',
        self::TERMS => 'Пользовательское соглашение',
        self::PDN_CONSENT => 'Согласие на обработку ПДн',
        self::COOKIES => 'Политика cookie',
        self::SELLER_INFO => 'Реквизиты и информация о продавце',
    ];

    public const ROUTES = [
        self::PRIVACY => 'main.privacy',
        self::OFFER => 'main.offer',
        self::TERMS => 'main.terms',
        self::PDN_CONSENT => 'main.pdnConsent',
        self::COOKIES => 'main.cookies',
        self::SELLER_INFO => 'main.seller',
    ];

    public static function isValid(string $type): bool
    {
        return in_array($type, self::ALL, true);
    }
}
