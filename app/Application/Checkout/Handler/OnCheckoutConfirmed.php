<?php

namespace App\Application\Checkout\Handler;

use App\Domain\Checkout\Event\CheckoutConfirmed;

/**
 * Заготовка под реакцию Order BC на подтверждение чекаута.
 */
final class OnCheckoutConfirmed
{
    public function handle(CheckoutConfirmed $event): void
    {
        // Order BC подключится позже.
    }
}
