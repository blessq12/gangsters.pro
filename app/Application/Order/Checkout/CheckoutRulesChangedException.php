<?php

namespace App\Application\Order\Checkout;

/**
 * Фронт считал корзину по устаревшим правилам или его итог не совпал с серверным пересчётом.
 */
final class CheckoutRulesChangedException extends \RuntimeException
{
    public const REASON_RULES_CHANGED = 'rules_changed';

    public const REASON_TOTAL_MISMATCH = 'total_mismatch';

    public function __construct(
        public readonly string $reason,
        public readonly int $branchId,
    ) {
        parent::__construct(match ($reason) {
            self::REASON_TOTAL_MISMATCH => 'Сумма заказа изменилась. Проверь заказ и отправь ещё раз.',
            default => 'Цены или условия обновились. Проверь заказ и отправь ещё раз.',
        });
    }
}
