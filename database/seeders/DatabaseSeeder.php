<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            FilamentAdminUserSeeder::class,
            CatalogSeeder::class,
            PromotionSeeder::class,
            DeliverySeeder::class,
            CompanySeeder::class,
            MarketingContentSeeder::class,
        ]);
    }
}
