<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminLegalMenuSeeder extends Seeder
{
    private function connectionName(): string
    {
        return config('admin.database.connection') ?: config('database.default');
    }

    private function db()
    {
        return DB::connection($this->connectionName());
    }

    public function run(): void
    {
        $conn = $this->connectionName();

        try {
            if (!Schema::connection($conn)->hasTable('admin_menu')) {
                $this->command?->warn('Таблица admin_menu отсутствует — пропуск.');
                return;
            }
        } catch (\Throwable $e) {
            $this->command?->warn('Нет доступа к admin DB: '.$e->getMessage());
            return;
        }

        $now = now();

        if ($this->db()->table('admin_menu')->where('uri', 'legal-documents')->exists()) {
            $this->command?->info('Пункты меню юрдоков уже есть.');
            $this->ensureRoleBinding();
            return;
        }

        $parentId = $this->db()->table('admin_menu')->insertGetId([
            'parent_id' => 0,
            'order' => ((int) $this->db()->table('admin_menu')->max('order')) + 1,
            'title' => 'Юридическое',
            'icon' => 'fa-balance-scale',
            'uri' => '',
            'permission' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->db()->table('admin_menu')->insert([
            [
                'parent_id' => $parentId,
                'order' => ((int) $this->db()->table('admin_menu')->max('order')) + 1,
                'title' => 'Документы',
                'icon' => 'fa-file-text-o',
                'uri' => 'legal-documents',
                'permission' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'parent_id' => $parentId,
                'order' => ((int) $this->db()->table('admin_menu')->max('order')) + 2,
                'title' => 'Согласия',
                'icon' => 'fa-check-square-o',
                'uri' => 'legal-consents',
                'permission' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $this->attachRole($parentId);
        $this->command?->info('Меню «Юридическое» добавлено.');
    }

    private function ensureRoleBinding(): void
    {
        $parentId = $this->db()->table('admin_menu')
            ->where('title', 'Юридическое')
            ->where('parent_id', 0)
            ->value('id');

        if ($parentId) {
            $this->attachRole((int) $parentId);
        }
    }

    private function attachRole(int $menuId): void
    {
        $conn = $this->connectionName();

        if (!Schema::connection($conn)->hasTable('admin_roles')
            || !Schema::connection($conn)->hasTable('admin_role_menu')) {
            return;
        }

        $roleId = $this->db()->table('admin_roles')->where('slug', 'administrator')->value('id');
        if (!$roleId) {
            return;
        }

        $exists = $this->db()->table('admin_role_menu')
            ->where('role_id', $roleId)
            ->where('menu_id', $menuId)
            ->exists();

        if (!$exists) {
            $payload = [
                'role_id' => $roleId,
                'menu_id' => $menuId,
            ];

            if (Schema::connection($conn)->hasColumn('admin_role_menu', 'created_at')) {
                $payload['created_at'] = now();
                $payload['updated_at'] = now();
            }

            $this->db()->table('admin_role_menu')->insert($payload);
        }
    }
}
