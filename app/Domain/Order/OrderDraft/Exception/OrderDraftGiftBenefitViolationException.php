<?php

namespace App\Domain\Order\OrderDraft\Exception;

use RuntimeException;

final class OrderDraftGiftBenefitViolationException extends RuntimeException
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
        return new self('Некорректная строка подарка в корзине.');
    }

    public static function giftRequired(): self
    {
        return new self('Выбери подарок, чтобы подтвердить заказ.');
    }
}
