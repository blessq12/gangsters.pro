<?php

namespace App\Application\Operations\Delivery\Support;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Operations\Delivery\DTO\UpdateDeliverySettingsDto;

final class DeliverySettingsValidator
{
    private const MAX_AVERAGE_DELIVERY_MINUTES = 1440;

    public static function assertValid(UpdateDeliverySettingsDto $dto): void
    {
        self::assertNonNegative('min_order_amount_kopecks', $dto->minOrderAmountKopecks);
        self::assertNonNegative('delivery_fee_kopecks', $dto->deliveryFeeKopecks);
        self::assertNonNegative('average_delivery_time_minutes', $dto->averageDeliveryTimeMinutes);

        if ($dto->averageDeliveryTimeMinutes !== null
            && $dto->averageDeliveryTimeMinutes > self::MAX_AVERAGE_DELIVERY_MINUTES) {
            throw new ApiException(
                'Среднее время доставки не может превышать '.self::MAX_AVERAGE_DELIVERY_MINUTES.' мин.',
                422,
            );
        }
    }

    private static function assertNonNegative(string $field, ?int $value): void
    {
        if ($value !== null && $value < 0) {
            throw new ApiException("Поле {$field} не может быть отрицательным.", 422);
        }
    }
}
