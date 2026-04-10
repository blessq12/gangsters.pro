<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Удаляет ключ delivery из JSON work_schedule (список по дням или объект по ключам дней).
     */
    public function up(): void
    {
        $rows = DB::table('companies')
            ->select(['id', 'work_schedule'])
            ->whereNotNull('work_schedule')
            ->get();

        foreach ($rows as $row) {
            $raw = $row->work_schedule;
            if ($raw === null || $raw === '') {
                continue;
            }

            $decoded = is_string($raw) ? json_decode($raw, true) : $raw;
            if (! is_array($decoded)) {
                continue;
            }

            $strip = function (array &$item): bool {
                if (! array_key_exists('delivery', $item)) {
                    return false;
                }
                unset($item['delivery']);

                return true;
            };

            $changed = false;
            if (array_is_list($decoded)) {
                foreach ($decoded as &$item) {
                    if (is_array($item) && $strip($item)) {
                        $changed = true;
                    }
                }
                unset($item);
            } else {
                foreach ($decoded as &$item) {
                    if (is_array($item) && $strip($item)) {
                        $changed = true;
                    }
                }
                unset($item);
            }

            if ($changed) {
                DB::table('companies')
                    ->where('id', $row->id)
                    ->update([
                        'work_schedule' => json_encode($decoded, JSON_UNESCAPED_UNICODE),
                    ]);
            }
        }
    }

    public function down(): void
    {
        // Данные без бэкапа не восстанавливаем
    }
};
