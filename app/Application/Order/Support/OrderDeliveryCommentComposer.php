<?php

namespace App\Application\Order\Support;

use App\Shared\Enum\PaymentMethod;

/**
 * Сборка итогового комментария доставки для сохранённого заказа.
 */
final class OrderDeliveryCommentComposer
{
    public static function compose(
        ?string $deliveryComment,
        PaymentMethod $paymentMethod,
        ?int $changeFromRubles,
    ): ?string {
        $comment = self::normalizeComment($deliveryComment);
        $changeNote = self::buildCashChangeNote($paymentMethod, $changeFromRubles);

        if ($changeNote === null) {
            return $comment;
        }

        if ($comment === null) {
            return $changeNote;
        }

        return $comment.'. '.$changeNote;
    }

    private static function normalizeComment(?string $comment): ?string
    {
        if ($comment === null) {
            return null;
        }

        $trimmed = trim($comment);

        return $trimmed !== '' ? $trimmed : null;
    }

    private static function buildCashChangeNote(
        PaymentMethod $paymentMethod,
        ?int $changeFromRubles,
    ): ?string {
        if ($paymentMethod !== PaymentMethod::Cash) {
            return null;
        }

        if ($changeFromRubles === null || $changeFromRubles <= 0) {
            return null;
        }

        return sprintf('Сдача с %d ₽', $changeFromRubles);
    }
}
