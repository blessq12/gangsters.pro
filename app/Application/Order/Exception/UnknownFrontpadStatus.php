<?php

namespace App\Application\Order\Exception;

final class UnknownFrontpadStatus extends \RuntimeException
{
    public function __construct(
        public readonly int $frontpadStatus,
    ) {
        parent::__construct(sprintf('Неизвестный статус Frontpad: %d', $frontpadStatus));
    }
}
