<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Stories
        Schema::dropIfExists('stories');

        // Banners and related tables
        Schema::dropIfExists('banners');
        Schema::dropIfExists('mini_banners'); // на случай доп. таблиц из старых миграций

        // Settings
        Schema::dropIfExists('settings');

        // Work schedules (если есть, дропаем безопасно, включая старый typo)
        Schema::dropIfExists('work_schedules');
        Schema::dropIfExists('work_schedule_items');
        Schema::dropIfExists('work_shedules');

        // Vacancies
        Schema::dropIfExists('vacancies');
    }

    public function down(): void
    {
        // Ничего не поднимаем обратно — структура считается легаси.
        // При необходимости восстановления — откатываемся к старым миграциям.
    }
};

