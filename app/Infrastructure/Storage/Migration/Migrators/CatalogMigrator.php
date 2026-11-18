<?php

namespace App\Infrastructure\Storage\Migration\Migrators;

use App\Infrastructure\Storage\Migration\Contract\BoundedContextMigrator;
use App\Infrastructure\Storage\Migration\MigrationReport;
use App\Infrastructure\Storage\Migration\Support\ConnectionSchema;
use Illuminate\Database\ConnectionInterface;

final class CatalogMigrator implements BoundedContextMigrator
{
    private const KOPECKS_IN_RUBLE = 100;

    public function key(): string
    {
        return 'catalog';
    }

    public function migrate(
        ConnectionInterface $source,
        ConnectionInterface $target,
        bool $dryRun,
        bool $truncateTarget,
    ): MigrationReport {
        $report = new MigrationReport();

        if (! ConnectionSchema::hasTable($source, 'PRD_products')) {
            $report->skip('catalog: нет PRD_products на source');

            return $report;
        }

        if ($truncateTarget && ! $dryRun) {
            ConnectionSchema::truncate($target, [
                'CAT_set_lines',
                'CAT_item_images',
                'CAT_item_tags',
                'CAT_category_items',
                'CAT_prices',
                'CAT_items',
                'CAT_tags',
                'CAT_categories',
            ]);
        }

        $this->copyCategories($source, $target, $dryRun, $report);
        $this->copyTags($source, $target, $dryRun, $report);
        $this->copyItemsAndPrices($source, $target, $dryRun, $report);
        $this->copyCategoryItems($source, $target, $dryRun, $report);
        $this->copyItemTags($source, $target, $dryRun, $report);
        $this->copySetLines($source, $target, $dryRun, $report);
        $this->copyImages($source, $target, $dryRun, $report);

        return $report;
    }

    private function copyCategories(
        ConnectionInterface $source,
        ConnectionInterface $target,
        bool $dryRun,
        MigrationReport $report,
    ): void {
        if (! ConnectionSchema::hasTable($source, 'PRD_categories')) {
            $report->skip('catalog: нет PRD_categories');

            return;
        }

        $rows = $source->table('PRD_categories')->orderBy('id')->get();
        $report->addRead('PRD_categories', $rows->count());
        if ($dryRun) {
            return;
        }

        foreach ($rows as $row) {
            $target->table('CAT_categories')->updateOrInsert(
                ['id' => $row->id],
                [
                    'name' => $row->name,
                    'slug' => $row->slug,
                    'sort_order' => (int) ($row->sort_order ?? 0),
                    'is_active' => (bool) ($row->is_active ?? true),
                    'is_accompanying' => (bool) ($row->is_accompanying ?? false),
                    'created_at' => $row->created_at ?? now(),
                    'updated_at' => $row->updated_at ?? now(),
                ],
            );
        }
        $report->addWritten('CAT_categories', $rows->count());
    }

    private function copyTags(
        ConnectionInterface $source,
        ConnectionInterface $target,
        bool $dryRun,
        MigrationReport $report,
    ): void {
        if (! ConnectionSchema::hasTable($source, 'PRD_tags')) {
            $report->skip('catalog: нет PRD_tags');

            return;
        }

        $rows = $source->table('PRD_tags')->orderBy('id')->get();
        $report->addRead('PRD_tags', $rows->count());
        if ($dryRun) {
            return;
        }

        foreach ($rows as $row) {
            $target->table('CAT_tags')->updateOrInsert(
                ['id' => $row->id],
                [
                    'code' => $row->code,
                    'label' => $row->label,
                    'color' => $row->color ?? 'amber',
                    'is_active' => (bool) ($row->is_active ?? true),
                    'sort_order' => (int) ($row->sort_order ?? 0),
                    'created_at' => $row->created_at ?? now(),
                    'updated_at' => $row->updated_at ?? now(),
                ],
            );
        }
        $report->addWritten('CAT_tags', $rows->count());
    }

    private function copyItemsAndPrices(
        ConnectionInterface $source,
        ConnectionInterface $target,
        bool $dryRun,
        MigrationReport $report,
    ): void {
        $rows = $source->table('PRD_products')->orderBy('id')->get();
        $report->addRead('PRD_products', $rows->count());
        if ($dryRun) {
            $report->note('catalog: цены → CAT_prices (regular/old); meta_* → PRM (promotion migrator)');

            return;
        }

        $pricesWritten = 0;
        foreach ($rows as $row) {
            $nutrition = null;
            $calories = (float) ($row->calories ?? 0);
            $proteins = (float) ($row->proteins ?? 0);
            $fats = (float) ($row->fats ?? 0);
            $carbs = (float) ($row->carbs ?? 0);
            if ($calories > 0 || $proteins > 0 || $fats > 0 || $carbs > 0) {
                $nutrition = json_encode([
                    'calories' => $calories,
                    'proteins' => $proteins,
                    'fats' => $fats,
                    'carbs' => $carbs,
                    'basis' => (string) ($row->nutrition_basis ?? 'per_100g'),
                ], JSON_UNESCAPED_UNICODE);
            }

            $ingredients = $row->ingredients ?? null;
            if (is_array($ingredients)) {
                $ingredients = json_encode($ingredients, JSON_UNESCAPED_UNICODE);
            }

            $target->table('CAT_items')->updateOrInsert(
                ['id' => $row->id],
                [
                    'name' => $row->name,
                    'slug' => $row->slug,
                    'sku' => $row->sku ?? null,
                    'description' => $row->description ?? null,
                    'status' => $row->status ?? 'active',
                    'kind' => $row->catalog_kind ?? 'product',
                    'is_system' => (bool) ($row->is_system ?? false),
                    'weight' => isset($row->weight) ? (int) $row->weight : null,
                    'nutrition' => $nutrition,
                    'ingredients' => $ingredients,
                    'archived_at' => $row->archived_at ?? null,
                    'created_at' => $row->created_at ?? now(),
                    'updated_at' => $row->updated_at ?? now(),
                ],
            );

            $regularRubles = max(0, (int) ($row->price ?? 0));
            $target->table('CAT_prices')->updateOrInsert(
                ['item_id' => $row->id, 'type' => 'regular'],
                [
                    'amount_kopecks' => $regularRubles * self::KOPECKS_IN_RUBLE,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
            $pricesWritten++;

            $oldRubles = isset($row->old_price) ? (int) $row->old_price : 0;
            if ($oldRubles > 0) {
                $target->table('CAT_prices')->updateOrInsert(
                    ['item_id' => $row->id, 'type' => 'old'],
                    [
                        'amount_kopecks' => $oldRubles * self::KOPECKS_IN_RUBLE,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                );
                $pricesWritten++;
            }
        }

        $report->addWritten('CAT_items', $rows->count());
        $report->addWritten('CAT_prices', $pricesWritten);
    }

    private function copyCategoryItems(
        ConnectionInterface $source,
        ConnectionInterface $target,
        bool $dryRun,
        MigrationReport $report,
    ): void {
        if (! ConnectionSchema::hasTable($source, 'PRD_category_product')) {
            $report->skip('catalog: нет PRD_category_product');

            return;
        }

        $rows = $source->table('PRD_category_product')->orderBy('id')->get();
        $report->addRead('PRD_category_product', $rows->count());
        if ($dryRun) {
            return;
        }

        foreach ($rows as $row) {
            $target->table('CAT_category_items')->updateOrInsert(
                [
                    'category_id' => $row->category_id,
                    'item_id' => $row->product_id,
                ],
                [
                    'sort_order' => (int) ($row->sort_order ?? 0),
                ],
            );
        }
        $report->addWritten('CAT_category_items', $rows->count());
    }

    private function copyItemTags(
        ConnectionInterface $source,
        ConnectionInterface $target,
        bool $dryRun,
        MigrationReport $report,
    ): void {
        if (! ConnectionSchema::hasTable($source, 'PRD_product_tag')) {
            $report->skip('catalog: нет PRD_product_tag');

            return;
        }

        $rows = $source->table('PRD_product_tag')->orderBy('id')->get();
        $report->addRead('PRD_product_tag', $rows->count());
        if ($dryRun) {
            return;
        }

        foreach ($rows as $row) {
            $target->table('CAT_item_tags')->updateOrInsert(
                [
                    'item_id' => $row->product_id,
                    'tag_id' => $row->tag_id,
                ],
                [],
            );
        }
        $report->addWritten('CAT_item_tags', $rows->count());
    }

    private function copySetLines(
        ConnectionInterface $source,
        ConnectionInterface $target,
        bool $dryRun,
        MigrationReport $report,
    ): void {
        if (! ConnectionSchema::hasTable($source, 'PRD_product_set_lines')) {
            $report->skip('catalog: нет PRD_product_set_lines');

            return;
        }

        $rows = $source->table('PRD_product_set_lines')->orderBy('id')->get();
        $report->addRead('PRD_product_set_lines', $rows->count());
        if ($dryRun) {
            return;
        }

        foreach ($rows as $row) {
            $setId = $row->set_id ?? $row->set_item_id ?? null;
            $componentId = $row->product_id ?? $row->component_item_id ?? null;
            if ($setId === null || $componentId === null) {
                continue;
            }

            $target->table('CAT_set_lines')->updateOrInsert(
                [
                    'set_item_id' => $setId,
                    'component_item_id' => $componentId,
                ],
                [
                    'quantity' => max(1, (int) ($row->quantity ?? 1)),
                ],
            );
        }
        $report->addWritten('CAT_set_lines', $rows->count());
    }

    private function copyImages(
        ConnectionInterface $source,
        ConnectionInterface $target,
        bool $dryRun,
        MigrationReport $report,
    ): void {
        if (! ConnectionSchema::hasTable($source, 'PRD_product_images')) {
            $report->skip('catalog: нет PRD_product_images');

            return;
        }

        $rows = $source->table('PRD_product_images')->orderBy('id')->get();
        $report->addRead('PRD_product_images', $rows->count());
        if ($dryRun) {
            return;
        }

        foreach ($rows as $row) {
            $focus = null;
            if (isset($row->focus_x, $row->focus_y) && $row->focus_x !== null && $row->focus_y !== null) {
                $focus = json_encode([
                    'x' => (float) $row->focus_x,
                    'y' => (float) $row->focus_y,
                ], JSON_UNESCAPED_UNICODE);
            }

            $target->table('CAT_item_images')->updateOrInsert(
                ['id' => $row->id],
                [
                    'item_id' => $row->product_id,
                    'path' => $row->path,
                    'disk' => $row->disk ?? 'public',
                    'sort_order' => (int) ($row->sort_order ?? 0),
                    'focus' => $focus,
                    'created_at' => $row->created_at ?? now(),
                    'updated_at' => $row->updated_at ?? now(),
                ],
            );
        }
        $report->addWritten('CAT_item_images', $rows->count());
    }
}
