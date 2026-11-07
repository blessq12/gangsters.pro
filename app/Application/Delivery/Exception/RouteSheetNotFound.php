<?php

namespace App\Application\Delivery\Exception;

final class RouteSheetNotFound extends \RuntimeException
{
    public static function forId(int $id): self
    {
        return new self('Маршрутный лист не найден.');
    }
}
