<?php

namespace App\Support\Order;

use App\Domain\Order\Enums\PaymentStatus;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

final class OrderStatusLabels
{
    /**
     * @return list<string>
     */
    public static function statusTabKeys(): array
    {
        return ['all', 'new', 'preparing', 'in_transit', 'delivered'];
    }

    public static function statusTabLabel(string $key): string
    {
        return match ($key) {
            'all' => 'Все',
            'new' => 'Новые',
            'preparing' => 'Готовятся',
            'in_transit' => 'В пути',
            'delivered' => 'Доставлены',
            default => self::statusLabel($key),
        };
    }

    public static function statusTabIcon(string $key): string|BackedEnum
    {
        return match ($key) {
            'all' => Heroicon::OutlinedQueueList,
            'new' => Heroicon::OutlinedInbox,
            'preparing' => Heroicon::OutlinedFire,
            'in_transit' => Heroicon::OutlinedTruck,
            'delivered' => Heroicon::OutlinedCheckCircle,
            default => Heroicon::OutlinedRectangleStack,
        };
    }

    /**
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return [
            'new' => 'Новый',
            'preparing' => 'Готовится',
            'in_transit' => 'В пути',
            'delivered' => 'Доставлен',
        ];
    }

    public static function statusLabel(string $status): string
    {
        return self::statusOptions()[$status] ?? $status;
    }

    public static function statusColor(string $status): string
    {
        return match ($status) {
            'new' => 'info',
            'preparing' => 'warning',
            'in_transit' => 'primary',
            'delivered' => 'success',
            default => 'gray',
        };
    }

    public static function paymentStatusColor(?string $status): string
    {
        return match ($status) {
            PaymentStatus::Unpaid->value => 'danger',
            PaymentStatus::Processing->value => 'warning',
            PaymentStatus::Paid->value => 'success',
            default => 'gray',
        };
    }

    /**
     * @param  array<string, mixed>|null  $address
     */
    public static function formatAddress(?array $address): ?string
    {
        if ($address === null || $address === []) {
            return null;
        }

        $parts = array_filter([
            $address['street'] ?? null,
            isset($address['house']) ? 'д. '.$address['house'] : null,
            isset($address['entrance']) ? 'подъезд '.$address['entrance'] : null,
            isset($address['apartment']) ? 'кв. '.$address['apartment'] : null,
        ]);

        return $parts === [] ? null : implode(', ', $parts);
    }
}
