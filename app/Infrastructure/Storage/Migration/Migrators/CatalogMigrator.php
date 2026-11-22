<?php

namespace App\Infrastructure\Storage\Migration\Migrators;

use App\Infrastructure\Storage\Migration\Contract\BoundedContextMigrator;
use App\Infrastructure\Storage\Migration\MigrationReport;
use App\Infrastructure\Storage\Migration\Support\ConnectionSchema;
use Illuminate\Database\ConnectionInterface;

/**
 * Переносит только справочник товаров (CAT_items + CAT_item_images).
 * Категории, теги, сеты и цены живут в филиале (CMP_*) и этим мигратором не переносятся;
 * позиции-сеты (catalog_kind = set) пропускаются.
 */
final class CatalogMigrator implements BoundedContextMigrator
{
    private const SET_KIND = 'set';

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
                'CAT_item_images',
                'CAT_items',
            ]);
        }

        $skippedSetIds = $this->copyItems($source, $target, $dryRun, $report);
        $this->copyImages($source, $target, $dryRun, $report, $skippedSetIds);

        return $report;
    }

    /**
     * @return list<int> id позиций-сетов, которые не переносились
     */
    private function copyItems(
        ConnectionInterface $source,
        ConnectionInterface $target,
        bool $dryRun,
        MigrationReport $report,
    ): array {
        $rows = $source->table('PRD_products')->orderBy('id')->get();
        $report->addRead('PRD_products', $rows->count());

        $skippedSetIds = [];
        $written = 0;
        $report->note('catalog: цены/категории/теги/сеты не переносятся (меню филиала — CMP_*); meta_* → PRM (promotion migrator)');
        if ($dryRun) {
            return $skippedSetIds;
        }

        foreach ($rows as $row) {
            if (($row->catalog_kind ?? 'product') === self::SET_KIND) {
                $skippedSetIds[] = (int) $row->id;

                continue;
            }

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
                    'is_system' => (bool) ($row->is_system ?? false),
                    'weight' => isset($row->weight) ? (int) $row->weight : null,
                    'nutrition' => $nutrition,
                    'ingredients' => $ingredients,
                    'archived_at' => $row->archived_at ?? null,
                    'created_at' => $row->created_at ?? now(),
                    'updated_at' => $row->updated_at ?? now(),
                ],
            );
            $written++;
        }

        $report->addWritten('CAT_items', $written);

        return $skippedSetIds;
    }

    /**
     * @param  list<int>  $skippedSetIds
     */
    private function copyImages(
        ConnectionInterface $source,
        ConnectionInterface $target,
        bool $dryRun,
        MigrationReport $report,
        array $skippedSetIds,
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

        $written = 0;
        foreach ($rows as $row) {
            if (in_array((int) $row->product_id, $skippedSetIds, true)) {
                continue;
            }

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
            $written++;
        }
        $report->addWritten('CAT_item_images', $written);
    }
}
