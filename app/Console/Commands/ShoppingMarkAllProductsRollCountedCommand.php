<?php

namespace App\Console\Commands;

use App\Domain\Product\Entity\Product;
use App\Infrastructure\Product\Model\PRD_Product;
use Illuminate\Console\Command;

/**
 * Разовая/тестовая массовая установка флага «считать в промо комплект к роллам».
 */
final class ShoppingMarkAllProductsRollCountedCommand extends Command
{
    protected $signature = 'shopping:mark-all-products-roll-counted {--only-active : Только товары со статусом active}';

    protected $description = 'Проставить cart_rule_counts_as_roll=true для товаров (для теста правила комплекта)';

    public function handle(): int
    {
        $query = PRD_Product::query();
        if ($this->option('only-active')) {
            $query->where('status', Product::STATUS_ACTIVE);
        }

        $count = $query->update(['cart_rule_counts_as_roll' => true]);

        $this->info("Обновлено строк: {$count}.");

        return self::SUCCESS;
    }
}
