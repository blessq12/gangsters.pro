<?php

namespace App\Infrastructure\Storage\Migration\Migrators;

use App\Infrastructure\Storage\Migration\Contract\BoundedContextMigrator;
use App\Infrastructure\Storage\Migration\MigrationReport;
use App\Infrastructure\Storage\Migration\Support\ConnectionSchema;
use Illuminate\Database\ConnectionInterface;

final class MarketingMigrator implements BoundedContextMigrator
{
    public function key(): string
    {
        return 'marketing';
    }

    public function migrate(
        ConnectionInterface $source,
        ConnectionInterface $target,
        bool $dryRun,
        bool $truncateTarget,
    ): MigrationReport {
        $report = new MigrationReport();

        if ($truncateTarget && ! $dryRun) {
            ConnectionSchema::truncate($target, [
                'MKT_delivery_conditions',
                'MKT_promotions',
                'MKT_banners',
            ]);
        }

        $this->copy(
            $source,
            $target,
            $dryRun,
            $report,
            'MKT_banners',
            'MKT_banners',
            static fn (object $row): array => [
                'image_desktop' => $row->image_desktop ?? $row->image ?? null,
                'image_mobile' => $row->image_mobile ?? $row->image ?? null,
                'sort_order' => (int) ($row->sort_order ?? 0),
                'is_active' => (bool) ($row->is_active ?? true),
                'created_at' => $row->created_at ?? now(),
                'updated_at' => $row->updated_at ?? now(),
            ],
        );

        $this->copy(
            $source,
            $target,
            $dryRun,
            $report,
            'MKT_promotions',
            'MKT_promotions',
            static fn (object $row): array => [
                'title' => $row->title,
                'body' => $row->body ?? null,
                'image' => $row->image ?? null,
                'sort_order' => (int) ($row->sort_order ?? 0),
                'is_active' => (bool) ($row->is_active ?? true),
                'created_at' => $row->created_at ?? now(),
                'updated_at' => $row->updated_at ?? now(),
            ],
        );

        return $report;
    }

    /**
     * @param  callable(object): array<string, mixed>  $map
     */
    private function copy(
        ConnectionInterface $source,
        ConnectionInterface $target,
        bool $dryRun,
        MigrationReport $report,
        string $sourceTable,
        string $targetTable,
        callable $map,
    ): void {
        if (! ConnectionSchema::hasTable($source, $sourceTable)) {
            $report->skip("marketing: нет {$sourceTable}");

            return;
        }

        $rows = $source->table($sourceTable)->orderBy('id')->get();
        $report->addRead($sourceTable, $rows->count());
        if ($dryRun) {
            return;
        }

        foreach ($rows as $row) {
            $target->table($targetTable)->updateOrInsert(
                ['id' => $row->id],
                $map($row),
            );
        }
        $report->addWritten($targetTable, $rows->count());
    }
}
