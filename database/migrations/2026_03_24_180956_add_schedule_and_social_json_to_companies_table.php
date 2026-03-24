<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->json('work_schedule')->nullable()->after('delivery_hours');
            $table->json('social_links')->nullable()->after('site_url');
        });

        $rows = DB::table('companies')
            ->select(['id', 'work_hours', 'delivery_hours', 'vk', 'inst', 'telegram', 'site_url'])
            ->get();

        foreach ($rows as $row) {
            $schedule = null;
            if ($row->work_hours || $row->delivery_hours) {
                $days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
                $schedule = [];
                foreach ($days as $day) {
                    $schedule[$day] = [
                        'work' => $row->work_hours ?: null,
                        'delivery' => $row->delivery_hours ?: null,
                        'is_day_off' => false,
                    ];
                }
            }

            $social = [];
            if (!empty($row->vk)) {
                $social[] = ['name' => 'VK', 'url' => $row->vk];
            }
            if (!empty($row->inst)) {
                $social[] = ['name' => 'Instagram', 'url' => $row->inst];
            }
            if (!empty($row->telegram)) {
                $social[] = ['name' => 'Telegram', 'url' => $row->telegram];
            }
            if (!empty($row->site_url)) {
                $social[] = ['name' => 'Сайт', 'url' => $row->site_url];
            }

            DB::table('companies')
                ->where('id', $row->id)
                ->update([
                    'work_schedule' => $schedule ? json_encode($schedule, JSON_UNESCAPED_UNICODE) : null,
                    'social_links' => $social !== [] ? json_encode($social, JSON_UNESCAPED_UNICODE) : null,
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'work_schedule',
                'social_links',
            ]);
        });
    }
};
