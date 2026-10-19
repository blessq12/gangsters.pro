<?php

namespace App\Application\Order\Exception;

final class OrderNotFoundForFrontpad extends \RuntimeException
{
    public function __construct(
        public readonly string $frontpadOrderId,
    ) {
        parent::__construct(sprintf('Заказ не найден по идентификатору Frontpad: %s', $frontpadOrderId));
    }
}
