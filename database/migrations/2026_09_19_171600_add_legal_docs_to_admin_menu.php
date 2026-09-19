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

    private function db()
    {
        return DB::connection($this->connectionName());
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

        $now = now();

        if (!$this->menuExists('legal-documents')) {
            $parentId = $this->db()->table('admin_menu')->insertGetId([
                'parent_id' => 0,
                'order' => $this->nextOrder(),
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
                    'order' => $this->nextOrder(),
                    'title' => 'Документы',
                    'icon' => 'fa-file-text-o',
                    'uri' => 'legal-documents',
                    'permission' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'parent_id' => $parentId,
                    'order' => $this->nextOrder(),
                    'title' => 'Согласия (аудит)',
                    'icon' => 'fa-check-square-o',
                    'uri' => 'legal-consents',
                    'permission' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);

            $this->attachAdministratorRole($parentId);
        } else {
            $docsId = $this->db()->table('admin_menu')->where('uri', 'legal-documents')->value('id');
            $parentId = $docsId
                ? (int) $this->db()->table('admin_menu')->where('id', $docsId)->value('parent_id')
                : 0;
            if ($parentId > 0) {
                $this->attachAdministratorRole($parentId);
            }
        }

        if (!$this->menuExists('legal-consents')) {
            $parentId = $this->db()->table('admin_menu')
                ->where('title', 'Юридическое')
                ->where('parent_id', 0)
                ->value('id');

            if ($parentId) {
                $this->db()->table('admin_menu')->insert([
                    'parent_id' => $parentId,
                    'order' => $this->nextOrder(),
                    'title' => 'Согласия (аудит)',
                    'icon' => 'fa-check-square-o',
                    'uri' => 'legal-consents',
                    'permission' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
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

        $ids = $this->db()->table('admin_menu')
            ->whereIn('uri', ['legal-documents', 'legal-consents'])
            ->pluck('id')
            ->all();

        $parentIds = $this->db()->table('admin_menu')
            ->where('title', 'Юридическое')
            ->where('parent_id', 0)
            ->pluck('id')
            ->all();

        $allIds = array_values(array_unique(array_merge($ids, $parentIds)));

        if ($allIds !== [] && Schema::connection($conn)->hasTable('admin_role_menu')) {
            $this->db()->table('admin_role_menu')->whereIn('menu_id', $allIds)->delete();
        }

        if ($ids !== []) {
            $this->db()->table('admin_menu')->whereIn('id', $ids)->delete();
        }

        if ($parentIds !== []) {
            $this->db()->table('admin_menu')->whereIn('id', $parentIds)->delete();
        }
    }

    private function menuExists(string $uri): bool
    {
        return $this->db()->table('admin_menu')->where('uri', $uri)->exists();
    }

    private function nextOrder(): int
    {
        return ((int) $this->db()->table('admin_menu')->max('order')) + 1;
    }

    private function attachAdministratorRole(int $menuId): void
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
};
