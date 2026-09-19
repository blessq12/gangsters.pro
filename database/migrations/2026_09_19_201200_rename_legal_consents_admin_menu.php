<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function connectionName(): string
    {
        return config('admin.database.connection') ?: config('database.default');
    }

    public function up(): void
    {
        $conn = $this->connectionName();

        try {
            if (!Schema::connection($conn)->hasTable('admin_menu')) {
                return;
            }
        } catch (\Throwable) {
            return;
        }

        DB::connection($conn)->table('admin_menu')
            ->where('uri', 'legal-consents')
            ->update([
                'title' => 'Согласия',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        $conn = $this->connectionName();

        try {
            if (!Schema::connection($conn)->hasTable('admin_menu')) {
                return;
            }
        } catch (\Throwable) {
            return;
        }

        DB::connection($conn)->table('admin_menu')
            ->where('uri', 'legal-consents')
            ->update([
                'title' => 'Согласия (аудит)',
                'updated_at' => now(),
            ]);
    }
};
