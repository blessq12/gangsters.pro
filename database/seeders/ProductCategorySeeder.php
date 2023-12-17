<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Nette\Utils\Random;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('product_categories')->insert([
            ['uri' => Random::generate() ,'name' => 'Роллы'],
            ['uri' => Random::generate() ,'name' => 'Запечённые роллы'],
            ['uri' => Random::generate() ,'name' => 'Темпурные роллы'],
            ['uri' => Random::generate() ,'name' => 'Вегетарианские роллы'],
            ['uri' => Random::generate() ,'name' => 'Мексика'],
            ['uri' => Random::generate() ,'name' => 'Воки'],
            ['uri' => Random::generate() ,'name' => 'Супы'],
            ['uri' => Random::generate() ,'name' => 'Закуски'],
            ['uri' => Random::generate() ,'name' => 'Наборы'],
            ['uri' => Random::generate() ,'name' => 'Напитки'],
            ['uri' => Random::generate() ,'name' => 'Салаты'],
        ]);
    }
}
