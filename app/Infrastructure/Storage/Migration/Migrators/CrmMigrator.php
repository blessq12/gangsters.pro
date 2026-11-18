<?php

namespace App\Infrastructure\Storage\Migration\Migrators;

use App\Infrastructure\Storage\Migration\Contract\BoundedContextMigrator;
use App\Infrastructure\Storage\Migration\MigrationReport;
use App\Infrastructure\Storage\Migration\Support\ConnectionSchema;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Str;

final class CrmMigrator implements BoundedContextMigrator
{
    public function key(): string
    {
        return 'crm';
    }

    public function migrate(
        ConnectionInterface $source,
        ConnectionInterface $target,
        bool $dryRun,
        bool $truncateTarget,
    ): MigrationReport {
        $report = new MigrationReport();

        if (! ConnectionSchema::hasTable($source, 'CRM_clients')) {
            $report->skip('crm: нет CRM_clients на source');

            return $report;
        }

        $sourceIsLegacy = ConnectionSchema::hasColumn($source, 'CRM_clients', 'password')
            || ConnectionSchema::hasColumn($source, 'CRM_clients', 'addresses')
            || ConnectionSchema::hasColumn($source, 'CRM_clients', 'favorite_product_ids');

        if (! $sourceIsLegacy
            && $source->getName() === $target->getName()
            && $source->getDatabaseName() === $target->getDatabaseName()
        ) {
            $report->skip('crm: source=target и CRM_clients уже в новой схеме');

            return $report;
        }

        if ($truncateTarget && ! $dryRun) {
            ConnectionSchema::truncate($target, [
                'CRM_client_favorites',
                'CRM_client_addresses',
                'CRM_clients',
            ]);
        }

        $rows = $source->table('CRM_clients')->orderBy('id')->get();
        $report->addRead('CRM_clients', $rows->count());
        if ($dryRun) {
            $report->note('crm: addresses JSON → CRM_client_addresses; favorites JSON → CRM_client_favorites; password → password_hash');

            return $report;
        }

        $addressesWritten = 0;
        $favoritesWritten = 0;

        foreach ($rows as $row) {
            $password = $row->password_hash ?? $row->password ?? '';

            $target->table('CRM_clients')->updateOrInsert(
                ['id' => $row->id],
                [
                    'name' => $row->name,
                    'phone' => $row->phone,
                    'email' => $row->email ?? null,
                    'birth_date' => $row->birth_date ?? null,
                    'password_hash' => $password,
                    'consent_personal_data' => (bool) ($row->consent_personal_data ?? false),
                    'consent_marketing' => (bool) ($row->consent_marketing ?? false),
                    'created_at' => $row->created_at ?? now(),
                    'updated_at' => $row->updated_at ?? now(),
                ],
            );

            $addresses = ConnectionSchema::decodeJson($row->addresses ?? null);
            if (is_array($addresses)) {
                foreach ($addresses as $address) {
                    if (! is_array($address)) {
                        continue;
                    }
                    $key = trim((string) ($address['id'] ?? ''));
                    if ($key === '') {
                        $key = Str::lower(bin2hex(random_bytes(8)));
                    }

                    $target->table('CRM_client_addresses')->updateOrInsert(
                        [
                            'client_id' => $row->id,
                            'address_key' => $key,
                        ],
                        [
                            'type' => $address['type'] ?? null,
                            'title' => $address['title'] ?? null,
                            'street' => (string) ($address['street'] ?? ''),
                            'house' => (string) ($address['house'] ?? ''),
                            'apartment' => $address['apartment'] ?? null,
                            'entrance' => $address['entrance'] ?? null,
                            'floor' => $address['floor'] ?? null,
                            'comment' => $address['comment'] ?? null,
                            'is_default' => (bool) ($address['is_default'] ?? false),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                    );
                    $addressesWritten++;
                }
            }

            $favorites = ConnectionSchema::decodeJson($row->favorite_product_ids ?? null);
            if (is_array($favorites)) {
                foreach ($favorites as $catalogItemId) {
                    $id = (int) $catalogItemId;
                    if ($id < 1) {
                        continue;
                    }
                    $target->table('CRM_client_favorites')->updateOrInsert(
                        [
                            'client_id' => $row->id,
                            'catalog_item_id' => $id,
                        ],
                        [
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                    );
                    $favoritesWritten++;
                }
            }
        }

        $report->addWritten('CRM_clients', $rows->count());
        $report->addWritten('CRM_client_addresses', $addressesWritten);
        $report->addWritten('CRM_client_favorites', $favoritesWritten);

        return $report;
    }
}
