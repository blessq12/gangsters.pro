<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Разовая чистка мусорных users (@example.com / @test.local).
 * Не трогает @gangsters.test и root@root.com. Skip production.
 */
class PruneLegacyTestUsersSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        User::query()
            ->where(function ($query): void {
                $query
                    ->where('email', 'like', '%@example.com')
                    ->orWhere('email', 'like', '%@test.local');
            })
            ->delete();
    }
}
