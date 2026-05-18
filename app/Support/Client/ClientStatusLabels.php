<?php

namespace App\Support\Client;

use App\Domain\Client\Entity\Client;

final class ClientStatusLabels
{
    /**
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return [
            Client::STATUS_ACTIVE => 'Активен',
            Client::STATUS_BLOCKED => 'Заблокирован',
        ];
    }

    public static function statusLabel(string $status): string
    {
        return self::statusOptions()[$status] ?? $status;
    }

    public static function statusColor(string $status): string
    {
        return match ($status) {
            Client::STATUS_ACTIVE => 'success',
            Client::STATUS_BLOCKED => 'danger',
            default => 'gray',
        };
    }
}
