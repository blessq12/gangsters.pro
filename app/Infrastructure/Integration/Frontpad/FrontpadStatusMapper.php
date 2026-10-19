<?php

namespace App\Infrastructure\Integration\Frontpad;

use App\Domain\Order\Enum\OrderStatus;

final class FrontpadStatusMapper
{
    public function toDomain(int $frontpadStatus): ?OrderStatus
    {
        return match ($frontpadStatus) {
            1 => OrderStatus::New,
            13 => OrderStatus::Preorder,
            3 => OrderStatus::Preparing,
            12 => OrderStatus::Ready,
            4 => OrderStatus::InTransit,
            11 => OrderStatus::Cancelled,
            10 => OrderStatus::Delivered,
            default => null,
        };
    }
}
