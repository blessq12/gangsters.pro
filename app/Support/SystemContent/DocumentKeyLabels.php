<?php

namespace App\Support\SystemContent;

final class DocumentKeyLabels
{
    public const PRIVACY_POLICY = 'privacy_policy';

    public const TERMS_OF_USE = 'terms_of_use';

    public const USER_AGREEMENT = 'user_agreement';

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return [
            self::PRIVACY_POLICY,
            self::TERMS_OF_USE,
            self::USER_AGREEMENT,
        ];
    }

    public static function label(string $key): string
    {
        return match ($key) {
            self::PRIVACY_POLICY => 'Политика конфиденциальности',
            self::TERMS_OF_USE => 'Правила использования',
            self::USER_AGREEMENT => 'Пользовательское соглашение',
            default => $key,
        };
    }

    public static function defaultTitle(string $key): string
    {
        return self::label($key);
    }
}
