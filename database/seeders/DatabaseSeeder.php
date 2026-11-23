<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CatalogSeeder::class,
            CompanySeeder::class,
            BranchMenuSeeder::class,
            DeliverySeeder::class,
            OrderCommercialRulesSeeder::class,
            TestEmployeesSeeder::class,
            TestClientSeeder::class,
        ]);
    }
}
