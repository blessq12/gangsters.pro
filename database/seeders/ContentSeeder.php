<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Контентные сидеры (каталог/компания/доставка/маркетинг/промо).
 * Только migrate:fresh или ручной --class=ContentSeeder. Skip production.
 */
class ContentSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $this->call([
            CompanySeeder::class,
            CatalogSeeder::class,
            BranchMenuSeeder::class,
            PromotionSeeder::class,
            DeliverySeeder::class,
            MarketingContentSeeder::class,
        ]);
    }
}
