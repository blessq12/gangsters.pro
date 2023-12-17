<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanyLegalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('company_legals')->insert([
            'company_id' => 1,
            'legal_form' => 'ИП',
            'legal_email' => 'millert@yandex.ru',
            'owner' => 'Тарасюк Наталья Витальевна',
            'inn' => '701711541008',
            'ogrn' => '319703100092535',
            'okpo' => '0174847807',
            'kpp' => '4567456',
            'registration_address' => 'ОБЛАСТЬ ТОМСКАЯ, район Асиновский, село Ново-Кусково',
        ]);
    }
}
