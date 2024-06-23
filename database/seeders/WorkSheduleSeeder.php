<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Company;

class WorkSheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companyId = Company::first()->id;

        DB::table('work_shedules')->insert([
            [
                'company_id' => $companyId,
                'day_eng' => 'monday',
                'day' => 'Понедельник',
                'open_time' => '09:00:00',
                'close_time' => '17:00:00',
                'day_off' => false,
            ],
            [
                'company_id' => $companyId,
                'day_eng' => 'tuesday',
                'day' => 'Вторник',
                'open_time' => '09:00:00',
                'close_time' => '17:00:00',
                'day_off' => false,
            ],
            [
                'company_id' => $companyId,
                'day_eng' => 'wednesday',
                'day' => 'Среда',
                'open_time' => '09:00:00',
                'close_time' => '17:00:00',
                'day_off' => false,
            ],
            [
                'company_id' => $companyId,
                'day_eng' => 'thursday',
                'day' => 'Четверг',
                'open_time' => '09:00:00',
                'close_time' => '17:00:00',
                'day_off' => false,
            ],
            [
                'company_id' => $companyId,
                'day_eng' => 'friday',
                'day' => 'Пятница',
                'open_time' => '09:00:00',
                'close_time' => '17:00:00',
                'day_off' => false,
            ],
            [
                'company_id' => $companyId,
                'day_eng' => 'saturday',
                'day' => 'Суббота',
                'open_time' => '10:00:00',
                'close_time' => '14:00:00',
                'day_off' => false,
            ],
            [
                'company_id' => $companyId,
                'day_eng' => 'sunday',
                'day' => 'Воскресенье',
                'open_time' => '09:00:00',
                'close_time' => '17:00:00',
                'day_off' => true,
            ]
        ]);
    }
}
