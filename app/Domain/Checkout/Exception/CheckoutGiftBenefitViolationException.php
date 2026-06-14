<?php

namespace App\Domain\Checkout\Exception;

use RuntimeException;

final class CheckoutGiftBenefitViolationException extends RuntimeException
{
    public static function notEligible(): self
    {
        return new self('Подарок недоступен при текущей сумме заказа.');
    }

    public static function invalidCandidate(): self
    {
        return new self('Выбранный подарок недоступен для акции.');
    }

    public static function invalidGiftLine(): self
    {
        return new self('Строка подарка в корзине некорректна.');
    }
}
