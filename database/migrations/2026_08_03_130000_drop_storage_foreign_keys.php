<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Убирает FK и индексы *_foreign: связанность сущностей — на слое приложения.
 */
return new class extends Migration
{
    /**
     * Таблицы, где в схеме когда-либо жили FK.
     *
     * @var list<string>
     */
    private array $tables = [
        'ORD_orders',
        'CRM_order_history',
        'CMP_company_legal',
        'CMP_company_documents',
        'PRD_category_product',
        'PRD_product_tag',
        'PRD_product_set_lines',
        'PRD_product_images',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $this->dropForeignKeys($table);
            $this->dropForeignNamedIndexes($table);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ORD_orders') && Schema::hasTable('CRM_clients')) {
            Schema::table('ORD_orders', function (Blueprint $table) {
                $table->foreign('client_id')
                    ->references('id')
                    ->on('CRM_clients')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasTable('CRM_order_history') && Schema::hasTable('CRM_clients')) {
            Schema::table('CRM_order_history', function (Blueprint $table) {
                $table->foreign('client_id')
                    ->references('id')
                    ->on('CRM_clients')
                    ->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('CMP_company_legal') && Schema::hasTable('CMP_company')) {
            Schema::table('CMP_company_legal', function (Blueprint $table) {
                $table->foreign('company_id')
                    ->references('id')
                    ->on('CMP_company')
                    ->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('CMP_company_documents') && Schema::hasTable('CMP_company')) {
            Schema::table('CMP_company_documents', function (Blueprint $table) {
                $table->foreign('company_id')
                    ->references('id')
                    ->on('CMP_company')
                    ->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('PRD_category_product')) {
            Schema::table('PRD_category_product', function (Blueprint $table) {
                $table->foreign('category_id')
                    ->references('id')
                    ->on('PRD_categories')
                    ->cascadeOnDelete();
                $table->foreign('product_id')
                    ->references('id')
                    ->on('PRD_products')
                    ->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('PRD_product_tag')) {
            Schema::table('PRD_product_tag', function (Blueprint $table) {
                $table->foreign('product_id')
                    ->references('id')
                    ->on('PRD_products')
                    ->cascadeOnDelete();
                $table->foreign('tag_id')
                    ->references('id')
                    ->on('PRD_tags')
                    ->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('PRD_product_set_lines')) {
            Schema::table('PRD_product_set_lines', function (Blueprint $table) {
                $table->foreign('set_id')
                    ->references('id')
                    ->on('PRD_products')
                    ->cascadeOnDelete();
                $table->foreign('product_id')
                    ->references('id')
                    ->on('PRD_products')
                    ->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('PRD_product_images')) {
            Schema::table('PRD_product_images', function (Blueprint $table) {
                $table->foreign('product_id')
                    ->references('id')
                    ->on('PRD_products')
                    ->cascadeOnDelete();
            });
        }
    }

    private function dropForeignKeys(string $table): void
    {
        $foreignKeys = Schema::getForeignKeys($table);

        if ($foreignKeys === []) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($foreignKeys): void {
            foreach ($foreignKeys as $foreignKey) {
                $name = $foreignKey['name'] ?? null;

                if (is_string($name) && $name !== '') {
                    $blueprint->dropForeign($name);

                    continue;
                }

                $blueprint->dropForeign($foreignKey['columns']);
            }
        });
    }

    private function dropForeignNamedIndexes(string $table): void
    {
        $indexes = Schema::getIndexes($table);

        foreach ($indexes as $index) {
            $name = $index['name'] ?? null;

            if (! is_string($name) || $name === '') {
                continue;
            }

            if (! str_ends_with($name, '_foreign')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($name): void {
                $blueprint->dropIndex($name);
            });
        }
    }
};
