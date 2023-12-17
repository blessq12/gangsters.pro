<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'name' => 'root',
            'tel' => '+7 (XXX) XXX-XX-XX',
            'email' => 'root@sushi',
            'admin' => true,
            'password' => Hash::make('password')
        ]);
    }
}
