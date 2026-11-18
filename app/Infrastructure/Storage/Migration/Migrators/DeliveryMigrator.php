<?php

namespace App\Infrastructure\Storage\Migration\Migrators;

use App\Infrastructure\Storage\Migration\Contract\BoundedContextMigrator;
use App\Infrastructure\Storage\Migration\MigrationReport;
use App\Infrastructure\Storage\Migration\Support\ConnectionSchema;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Str;

final class DeliveryMigrator implements BoundedContextMigrator
{
    public function key(): string
    {
        return 'delivery';
    }

    public function migrate(
        ConnectionInterface $source,
        ConnectionInterface $target,
        bool $dryRun,
        bool $truncateTarget,
    ): MigrationReport {
        $report = new MigrationReport();
        $hasLegacyConfig = ConnectionSchema::hasTable($source, 'DLV_configuration');
        $sourceConfig = $hasLegacyConfig ? 'DLV_configuration' : null;

        if ($sourceConfig === null) {
            $report->skip('delivery: нет DLV_configuration (legacy) на source — нужен --source-database со старой схемой');

            return $report;
        }

        if ($truncateTarget && ! $dryRun) {
            ConnectionSchema::truncate($target, [
                'DLV_route_stops',
                'DLV_route_sheets',
                'DLV_zones',
                'DLV_configurations',
            ]);
        }

        $configs = $source->table($sourceConfig)->orderBy('id')->get();
        $report->addRead($sourceConfig, $configs->count());

        $zonesWritten = 0;
        if (! $dryRun) {
            foreach ($configs as $row) {
                $target->table('DLV_configurations')->updateOrInsert(
                    ['id' => $row->id],
                    [
                        'min_order_amount_kopecks' => $row->min_order_amount_kopecks ?? null,
                        'average_delivery_time_minutes' => $row->average_delivery_time_minutes ?? null,
                        'company_kitchen_id' => $row->company_kitchen_id ?? null,
                        'created_at' => $row->created_at ?? now(),
                        'updated_at' => $row->updated_at ?? now(),
                    ],
                );

                $zones = ConnectionSchema::decodeJson($row->delivery_zones ?? null);
                if (! is_array($zones) || $zones === []) {
                    $geometry = ConnectionSchema::decodeJson($row->delivery_zone_geojson ?? null);
                    if (is_array($geometry)) {
                        $zones = [[
                            'id' => (string) Str::uuid(),
                            'name' => 'Основная',
                            'delivery_fee_kopecks' => max(0, (int) ($row->delivery_fee_kopecks ?? 0)),
                            'is_remote' => false,
                            'geometry' => $geometry,
                            'delivery_product_id' => null,
                        ]];
                    }
                }

                if (! is_array($zones)) {
                    continue;
                }

                $sort = 0;
                foreach ($zones as $zone) {
                    if (! is_array($zone)) {
                        continue;
                    }
                    $zoneKey = trim((string) ($zone['id'] ?? ''));
                    if ($zoneKey === '') {
                        $zoneKey = (string) Str::uuid();
                    }

                    $target->table('DLV_zones')->updateOrInsert(
                        [
                            'configuration_id' => $row->id,
                            'zone_key' => $zoneKey,
                        ],
                        [
                            'name' => (string) ($zone['name'] ?? 'Зона'),
                            'fee_kopecks' => max(0, (int) ($zone['delivery_fee_kopecks'] ?? $zone['fee_kopecks'] ?? 0)),
                            'is_remote' => (bool) ($zone['is_remote'] ?? false),
                            'geojson' => isset($zone['geometry'])
                                ? json_encode($zone['geometry'], JSON_UNESCAPED_UNICODE)
                                : null,
                            'delivery_product_id' => isset($zone['delivery_product_id'])
                                ? (int) $zone['delivery_product_id']
                                : null,
                            'sort_order' => $sort,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                    );
                    $zonesWritten++;
                    $sort++;
                }
            }
            $report->addWritten('DLV_configurations', $configs->count());
            $report->addWritten('DLV_zones', $zonesWritten);
        } else {
            $report->note('delivery: company_kitchen_id + delivery_zones → DLV_zones');
        }

        if (ConnectionSchema::hasTable($source, 'DLV_route_sheets')) {
            $sheets = $source->table('DLV_route_sheets')->orderBy('id')->get();
            $report->addRead('DLV_route_sheets', $sheets->count());
            if (! $dryRun) {
                foreach ($sheets as $row) {
                    $target->table('DLV_route_sheets')->updateOrInsert(
                        ['id' => $row->id],
                        [
                            'status' => $row->status,
                            'courier_id' => $row->courier_id ?? null,
                            'created_at' => $row->created_at ?? now(),
                            'updated_at' => $row->updated_at ?? now(),
                        ],
                    );
                }
                $report->addWritten('DLV_route_sheets', $sheets->count());
            }
        }

        if (ConnectionSchema::hasTable($source, 'DLV_route_stops')) {
            $stops = $source->table('DLV_route_stops')->orderBy('id')->get();
            $report->addRead('DLV_route_stops', $stops->count());
            if (! $dryRun) {
                foreach ($stops as $row) {
                    $target->table('DLV_route_stops')->updateOrInsert(
                        ['id' => $row->id],
                        [
                            'route_sheet_id' => $row->route_sheet_id,
                            'order_id' => $row->order_id,
                            'position' => (int) ($row->position ?? $row->sort_order ?? 0),
                            'status' => $row->status,
                            'settlement_note' => $row->settlement_note ?? $row->note ?? null,
                            'created_at' => $row->created_at ?? now(),
                            'updated_at' => $row->updated_at ?? now(),
                        ],
                    );
                }
                $report->addWritten('DLV_route_stops', $stops->count());
            }
        }

        return $report;
    }
}
