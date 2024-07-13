<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::create([
            'frontpad_api_key' => 'frontpad_api_key',
            'use_coin_system' => false,
            'coin_system_percent' => 0,
            'use_discount_system' => false,
            'discount_system_percent' => 0,
        ]);
    }
}
