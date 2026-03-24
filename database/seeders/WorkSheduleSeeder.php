<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Infrastructure\SystemContent\Model\SYS_Company;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WorkSheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companyId = SYS_Company::query()->first()?->id;

        if ($companyId === null) {
            return;
        }

        DB::table('work_shedules')->insert([
            [
                'company_id' => $companyId,
                'day_eng' => 'monday',
                'day' => 'Понедельник',
                'open_time' => '10:00:00',
                'close_time' => '20:00:00',
                'day_off' => false,
            ],
            [
                'company_id' => $companyId,
                'day_eng' => 'tuesday',
                'day' => 'Вторник',
                'open_time' => '10:00:00',
                'close_time' => '20:00:00',
                'day_off' => false,
            ],
            [
                'company_id' => $companyId,
                'day_eng' => 'wednesday',
                'day' => 'Среда',
                'open_time' => '10:00:00',
                'close_time' => '20:00:00',
                'day_off' => false,
            ],
            [
                'company_id' => $companyId,
                'day_eng' => 'thursday',
                'day' => 'Четверг',
                'open_time' => '10:00:00',
                'close_time' => '20:00:00',
                'day_off' => false,
            ],
            [
                'company_id' => $companyId,
                'day_eng' => 'friday',
                'day' => 'Пятница',
                'open_time' => '10:00:00',
                'close_time' => '20:00:00',
                'day_off' => false,
            ],
            [
                'company_id' => $companyId,
                'day_eng' => 'saturday',
                'day' => 'Суббота',
                'open_time' => '10:00:00',
                'close_time' => '20:00:00',
                'day_off' => false,
            ],
            [
                'company_id' => $companyId,
                'day_eng' => 'sunday',
                'day' => 'Воскресенье',
                'open_time' => '10:00:00',
                'close_time' => '20:00:00',
                'day_off' => true,
            ]
        ]);
    }
}
