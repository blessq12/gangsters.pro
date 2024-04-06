<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('companies')->insert([
            'name' => "Gangster's Sushi",
            'description' => 'Доставка готовой еды в Томске',
            'country' => 'Россия',
            'state' => 'Томская',
            'city' => 'Томск',
            'street' => 'ул. Говорова',
            'house' => '50/1',
            'phone' => '+7 (983) 234-84-84',
            'phone_additional' => '+7 (983) 234‒34‒38',
            'email_address' => 'gangstasushi@mail.ru',
        ]);
    }
}
