<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            ['uri' => 'client', 'name' => 'Клиент', 'desc' => 'Стандартная клиентская запись'],
            ['uri' => 'admin', 'name' => 'Администратор', 'desc' => 'Учетная запись Администратора'],
            ['uri' => 'root', 'name' => 'God mode', 'desc' => 'Полный доступ'],
        ]);
    }
}
