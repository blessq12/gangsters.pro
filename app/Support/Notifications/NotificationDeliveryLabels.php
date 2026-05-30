<?php

namespace App\Support\Notifications;

final class NotificationDeliveryLabels
{
    /** @return array<string, string> */
    public static function channelOptions(): array
    {
        return [
            'mail' => 'Email',
            'telegram' => 'Telegram',
            'sms' => 'SMS',
        ];
    }

    /** @return array<string, string> */
    public static function statusOptions(): array
    {
        return [
            'sent' => 'Отправлено',
            'failed' => 'Ошибка',
        ];
    }

    /** @return array<string, string> */
    public static function eventTypeOptions(): array
    {
        return [
            'password_reset' => 'Сброс пароля',
            'order_created' => 'Заказ создан',
            'profile_updated' => 'Профиль обновлён',
            'registration_welcome' => 'Приветствие',
        ];
    }

    public static function channelLabel(?string $channel): string
    {
        return self::channelOptions()[$channel ?? ''] ?? ($channel ?? '—');
    }

    public static function statusLabel(?string $status): string
    {
        return self::statusOptions()[$status ?? ''] ?? ($status ?? '—');
    }

    public static function eventTypeLabel(?string $eventType): string
    {
        return self::eventTypeOptions()[$eventType ?? ''] ?? ($eventType ?? '—');
    }

    public static function statusColor(?string $status): string
    {
        return match ($status) {
            'sent' => 'success',
            'failed' => 'danger',
            default => 'gray',
        };
    }
}
