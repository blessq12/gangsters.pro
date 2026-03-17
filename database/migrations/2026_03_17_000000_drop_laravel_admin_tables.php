<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Под старую админку использовалось отдельное подключение (config('admin.database.connection')),
     * поэтому явно указываем его здесь, чтобы корректно дропнуть таблицы независимо от default.
     */
    public function getConnection()
    {
        return config('admin.database.connection') ?: config('database.default');
    }

    public function up(): void
    {
        // Удаляем все таблицы laravel-admin, если они существуют.
        Schema::connection($this->getConnection())->dropIfExists('admin_operation_log');
        Schema::connection($this->getConnection())->dropIfExists('admin_role_menu');
        Schema::connection($this->getConnection())->dropIfExists('admin_user_permissions');
        Schema::connection($this->getConnection())->dropIfExists('admin_role_permissions');
        Schema::connection($this->getConnection())->dropIfExists('admin_role_users');
        Schema::connection($this->getConnection())->dropIfExists('admin_menu');
        Schema::connection($this->getConnection())->dropIfExists('admin_permissions');
        Schema::connection($this->getConnection())->dropIfExists('admin_roles');
        Schema::connection($this->getConnection())->dropIfExists('admin_users');
    }

    public function down(): void
    {
        // Специально оставляем down пустым: структура старой админки больше не поддерживается.
    }
};

