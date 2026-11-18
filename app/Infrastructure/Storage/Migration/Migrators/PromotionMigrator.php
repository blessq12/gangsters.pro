<?php

namespace App\Infrastructure\Storage\Migration\Migrators;

use App\Infrastructure\Storage\Migration\Contract\BoundedContextMigrator;
use App\Infrastructure\Storage\Migration\MigrationReport;
use App\Infrastructure\Storage\Migration\Support\ConnectionSchema;
use Illuminate\Database\ConnectionInterface;

final class PromotionMigrator implements BoundedContextMigrator
{
    private const KOPECKS_IN_RUBLE = 100;

    public function key(): string
    {
        return 'promotion';
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
                'PRM_product_roles',
                'PRM_zones',
                'PRM_policies',
            ]);
        }

        $this->migratePolicy($source, $target, $dryRun, $report);
        $this->migrateZones($source, $target, $dryRun, $report);
        $this->migrateRoles($source, $target, $dryRun, $report);
        $this->migrateRolesFromProductMeta($source, $target, $dryRun, $report);

        return $report;
    }

    private function migratePolicy(
        ConnectionInterface $source,
        ConnectionInterface $target,
        bool $dryRun,
        MigrationReport $report,
    ): void {
        if (! ConnectionSchema::hasTable($source, 'PRM_configuration')) {
            $report->skip('promotion: нет PRM_configuration (legacy) на source');

            return;
        }

        $rows = $source->table('PRM_configuration')->orderBy('id')->get();
        $report->addRead('PRM_configuration', $rows->count());
        if ($dryRun) {
            $report->note('promotion: flat PRM_configuration → PRM_policies JSON');

            return;
        }

        foreach ($rows as $row) {
            $giftActive = (bool) ($row->gift_benefit_active ?? true);
            $weekdays = ConnectionSchema::decodeJson($row->gift_courier_weekdays ?? null);
            if (! is_array($weekdays) || $weekdays === []) {
                $weekdays = [1, 2, 3, 4];
            }

            $payload = [
                'gift_rules' => json_encode([
                    [
                        'order_channel' => 'pickup',
                        'min_order_amount_kopecks' => (int) ($row->gift_pickup_min_order_kopecks ?? 0),
                        'benefit_type' => 'free_roll_gift',
                        'is_active' => $giftActive,
                        'allowed_weekdays' => [],
                    ],
                    [
                        'order_channel' => 'courier',
                        'min_order_amount_kopecks' => (int) ($row->gift_courier_min_order_kopecks ?? 0),
                        'benefit_type' => 'free_roll_gift',
                        'is_active' => $giftActive,
                        'allowed_weekdays' => array_values(array_map('intval', $weekdays)),
                    ],
                ], JSON_UNESCAPED_UNICODE),
                'delivery_benefit' => json_encode([
                    'free_delivery_threshold_kopecks' => (int) ($row->delivery_free_threshold_kopecks ?? 0),
                    'outside_zone_surcharge_kopecks' => (int) ($row->delivery_outside_zone_surcharge_kopecks ?? 0),
                    'below_threshold_fee_mode' => 'base_tariff',
                    'in_zone_at_threshold_fee_mode' => 'free',
                    'outside_zone_at_threshold_fee_mode' => 'outside_zone_surcharge_only',
                    'is_active' => (bool) ($row->delivery_benefit_active ?? true),
                ], JSON_UNESCAPED_UNICODE),
                'complement_set_benefit' => json_encode([
                    'rolls_per_set' => max(1, (int) ($row->complement_set_rolls_per_set ?? 2)),
                    'max_sets' => max(1, (int) ($row->complement_set_max_sets ?? 3)),
                    'is_active' => (bool) ($row->complement_set_benefit_active ?? true),
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => $row->created_at ?? now(),
                'updated_at' => $row->updated_at ?? now(),
            ];

            $target->table('PRM_policies')->updateOrInsert(['id' => $row->id], $payload);
        }
        $report->addWritten('PRM_policies', $rows->count());
    }

    private function migrateZones(
        ConnectionInterface $source,
        ConnectionInterface $target,
        bool $dryRun,
        MigrationReport $report,
    ): void {
        if (! ConnectionSchema::hasTable($source, 'PRM_zones')) {
            $report->skip('promotion: нет PRM_zones');

            return;
        }

        $rows = $source->table('PRM_zones')->orderBy('id')->get();
        $report->addRead('PRM_zones', $rows->count());
        if ($dryRun) {
            return;
        }

        foreach ($rows as $row) {
            $target->table('PRM_zones')->updateOrInsert(
                ['id' => $row->id],
                [
                    'name' => $row->name,
                    'fee_kopecks' => (int) ($row->fee_kopecks ?? 0),
                    'is_remote' => (bool) ($row->is_remote ?? false),
                    'created_at' => $row->created_at ?? now(),
                    'updated_at' => $row->updated_at ?? now(),
                ],
            );
        }
        $report->addWritten('PRM_zones', $rows->count());
    }

    private function migrateRoles(
        ConnectionInterface $source,
        ConnectionInterface $target,
        bool $dryRun,
        MigrationReport $report,
    ): void {
        if (! ConnectionSchema::hasTable($source, 'PRM_product_roles')) {
            $report->skip('promotion: нет PRM_product_roles');

            return;
        }

        $rows = $source->table('PRM_product_roles')->orderBy('id')->get();
        $report->addRead('PRM_product_roles', $rows->count());
        if ($dryRun) {
            return;
        }

        foreach ($rows as $row) {
            $catalogItemId = $row->catalog_item_id ?? $row->product_id ?? null;
            if ($catalogItemId === null) {
                continue;
            }

            $target->table('PRM_product_roles')->updateOrInsert(
                [
                    'catalog_item_id' => $catalogItemId,
                    'role' => $row->role,
                ],
                [
                    'name_snapshot' => $row->name_snapshot ?? $row->name ?? '',
                    'price_kopecks_snapshot' => (int) ($row->price_kopecks_snapshot ?? $row->price_kopecks ?? 0),
                    'paid_twin_catalog_item_id' => $row->paid_twin_catalog_item_id
                        ?? $row->paid_twin_product_id
                        ?? null,
                    'created_at' => $row->created_at ?? now(),
                    'updated_at' => $row->updated_at ?? now(),
                ],
            );
        }
        $report->addWritten('PRM_product_roles', $rows->count());
    }

    private function migrateRolesFromProductMeta(
        ConnectionInterface $source,
        ConnectionInterface $target,
        bool $dryRun,
        MigrationReport $report,
    ): void {
        if (! ConnectionSchema::hasTable($source, 'PRD_products')) {
            return;
        }

        $hasRoll = ConnectionSchema::hasColumn($source, 'PRD_products', 'meta_counts_as_roll');
        $hasComplement = ConnectionSchema::hasColumn($source, 'PRD_products', 'meta_is_complement_set');
        if (! $hasRoll && ! $hasComplement) {
            return;
        }

        $rows = $source->table('PRD_products')->orderBy('id')->get();
        $report->addRead('PRD_products(meta→roles)', $rows->count());
        if ($dryRun) {
            $report->note('promotion: meta_counts_as_roll/meta_is_complement_set/paid_twin → PRM_product_roles');

            return;
        }

        $written = 0;
        foreach ($rows as $row) {
            $priceKopecks = max(0, (int) ($row->price ?? 0)) * self::KOPECKS_IN_RUBLE;
            $name = (string) $row->name;

            if ($hasRoll && (bool) ($row->meta_counts_as_roll ?? false)) {
                $target->table('PRM_product_roles')->updateOrInsert(
                    ['catalog_item_id' => $row->id, 'role' => 'roll'],
                    [
                        'name_snapshot' => $name,
                        'price_kopecks_snapshot' => $priceKopecks,
                        'paid_twin_catalog_item_id' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                );
                $written++;
            }

            if ($hasComplement && (bool) ($row->meta_is_complement_set ?? false)) {
                $twin = isset($row->paid_twin_product_id) ? (int) $row->paid_twin_product_id : null;
                $target->table('PRM_product_roles')->updateOrInsert(
                    ['catalog_item_id' => $row->id, 'role' => 'complement'],
                    [
                        'name_snapshot' => $name,
                        'price_kopecks_snapshot' => $priceKopecks,
                        'paid_twin_catalog_item_id' => $twin > 0 ? $twin : null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                );
                $written++;
            }
        }
        $report->addWritten('PRM_product_roles(from meta)', $written);
    }
}
