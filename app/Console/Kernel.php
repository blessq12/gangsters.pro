<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Facades\TelegramMessage;
use App\Facades\YaMetrika;
use Illuminate\Support\Facades\Log;


class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function () {
            try {
                $statistic = YaMetrika::getTodayStatistic();
                TelegramMessage::sendMessage([
                    '🗓️ Статистика на: ' . $statistic->date . "\n",
                    '👥 Посетителей: ' . $statistic->visits,
                    '👤 Пользователей: ' . $statistic->users,
                    '👀 Просмотров: ' . $statistic->pageviews,
                    '🕒 Среднее время на сайте(минуты): ' . $statistic->avg_time_on_site,
                    '🔍 Глубина просмотра: ' . $statistic->page_depth,
                    '↪️ Процент отказа: ' . $statistic->bounce_rate,
                    '<b>Источники:</b> ' . "\n",
                    '➡️ Прямые: ' . $statistic->sources['direct'],
                    '🔍 Поиск: ' . $statistic->sources['search'],
                    '👥 Социальные: ' . $statistic->sources['social'],
                ], 'analytics');
            } catch (\Exception $e) {
                \Log::error('Kernel::schedule', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        })->everyThreeHours();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
