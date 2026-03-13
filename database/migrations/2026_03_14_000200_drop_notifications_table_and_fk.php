<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('notifications')) {
            Schema::table('notifications', function (Blueprint $table) {
                // Пытаемся аккуратно удалить FK на users, если он ещё существует.
                // Имя ключа по умолчанию в Laravel: notifications_user_id_foreign.
                try {
                    $table->dropForeign(['user_id']);
                } catch (\Throwable $e) {
                    // FK уже мог быть удалён ранее — игнорируем.
                }
            });

            Schema::dropIfExists('notifications');
        }
    }

    /**
     * Reverse the migrations.
     *
     * Для легаси-структуры down просто создаёт минимальный вариант таблицы,
     * без дополнительных колонок type/icon/session_id.
     */
    public function down(): void
    {
        if (! Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
                $table->string('message');
                $table->boolean('is_mass')->default(false);
                $table->boolean('is_read')->default(false);
                $table->timestamps();
            });
        }
    }
};

