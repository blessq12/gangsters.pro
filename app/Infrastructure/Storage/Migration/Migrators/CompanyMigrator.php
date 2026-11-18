<?php

namespace App\Infrastructure\Storage\Migration\Migrators;

use App\Infrastructure\Storage\Migration\Contract\BoundedContextMigrator;
use App\Infrastructure\Storage\Migration\MigrationReport;
use App\Infrastructure\Storage\Migration\Support\ConnectionSchema;
use Illuminate\Database\ConnectionInterface;

final class CompanyMigrator implements BoundedContextMigrator
{
    public function key(): string
    {
        return 'company';
    }

    public function migrate(
        ConnectionInterface $source,
        ConnectionInterface $target,
        bool $dryRun,
        bool $truncateTarget,
    ): MigrationReport {
        $report = new MigrationReport();
        $sourceCompany = ConnectionSchema::hasTable($source, 'CMP_company')
            ? 'CMP_company'
            : null;

        if ($sourceCompany === null) {
            if (ConnectionSchema::hasTable($source, 'CMP_companies')
                && $source->getName() === $target->getName()
                && $source->getDatabaseName() === $target->getDatabaseName()
            ) {
                $report->skip('company: source=target, CMP_companies уже целевая таблица');

                return $report;
            }

            // Допускаем копирование CMP_companies → CMP_companies между разными БД.
            if (! ConnectionSchema::hasTable($source, 'CMP_companies')) {
                $report->skip('company: нет CMP_company/CMP_companies на source');

                return $report;
            }
            $sourceCompany = 'CMP_companies';
        }

        if ($truncateTarget && ! $dryRun) {
            ConnectionSchema::truncate($target, [
                'CMP_kitchens',
                'CMP_branches',
                'CMP_company_documents',
                'CMP_company_legal',
                'CMP_companies',
            ]);
        }

        $companies = $source->table($sourceCompany)->orderBy('id')->get();
        $report->addRead($sourceCompany, $companies->count());
        if (! $dryRun) {
            foreach ($companies as $row) {
                $target->table('CMP_companies')->updateOrInsert(
                    ['id' => $row->id],
                    [
                        'name' => $row->name,
                        'description' => $row->description ?? null,
                        'phone' => $row->phone ?? null,
                        'email' => $row->email ?? null,
                        'socials' => is_string($row->socials ?? null)
                            ? $row->socials
                            : json_encode($row->socials ?? [], JSON_UNESCAPED_UNICODE),
                        'schedule' => is_string($row->schedule ?? null)
                            ? $row->schedule
                            : json_encode($row->schedule ?? [], JSON_UNESCAPED_UNICODE),
                        'created_at' => $row->created_at ?? now(),
                        'updated_at' => $row->updated_at ?? now(),
                    ],
                );
            }
            $report->addWritten('CMP_companies', $companies->count());
        }

        $this->copySimple(
            $source,
            $target,
            $dryRun,
            $report,
            'CMP_company_legal',
            'CMP_company_legal',
            static fn (object $row): array => [
                'id' => $row->id,
                'company_id' => $row->company_id,
                'full_name' => $row->full_name ?? null,
                'inn' => $row->inn ?? null,
                'ogrn' => $row->ogrn ?? null,
                'created_at' => $row->created_at ?? now(),
                'updated_at' => $row->updated_at ?? now(),
            ],
        );

        $this->copySimple(
            $source,
            $target,
            $dryRun,
            $report,
            'CMP_company_documents',
            'CMP_company_documents',
            static fn (object $row): array => [
                'id' => $row->id,
                'company_id' => $row->company_id,
                'slug' => $row->slug,
                'name' => $row->name,
                'content' => $row->content ?? null,
                'path' => $row->path ?? null,
                'created_at' => $row->created_at ?? now(),
                'updated_at' => $row->updated_at ?? now(),
            ],
        );

        $this->copySimple(
            $source,
            $target,
            $dryRun,
            $report,
            'CMP_branches',
            'CMP_branches',
            static fn (object $row): array => [
                'id' => $row->id,
                'company_id' => $row->company_id,
                'name' => $row->name,
                'is_active' => (bool) ($row->is_active ?? true),
                'created_at' => $row->created_at ?? now(),
                'updated_at' => $row->updated_at ?? now(),
            ],
        );

        $this->copySimple(
            $source,
            $target,
            $dryRun,
            $report,
            'CMP_kitchens',
            'CMP_kitchens',
            static fn (object $row): array => [
                'id' => $row->id,
                'branch_id' => $row->branch_id,
                'city' => $row->city ?? null,
                'street' => $row->street ?? null,
                'house' => $row->house ?? null,
                'comment' => $row->comment ?? null,
                'latitude' => $row->latitude ?? null,
                'longitude' => $row->longitude ?? null,
                'created_at' => $row->created_at ?? now(),
                'updated_at' => $row->updated_at ?? now(),
            ],
        );

        return $report;
    }

    /**
     * @param  callable(object): array<string, mixed>  $map
     */
    private function copySimple(
        ConnectionInterface $source,
        ConnectionInterface $target,
        bool $dryRun,
        MigrationReport $report,
        string $sourceTable,
        string $targetTable,
        callable $map,
    ): void {
        if (! ConnectionSchema::hasTable($source, $sourceTable)) {
            $report->skip("company: нет {$sourceTable}");

            return;
        }

        $rows = $source->table($sourceTable)->orderBy('id')->get();
        $report->addRead($sourceTable, $rows->count());
        if ($dryRun) {
            return;
        }

        foreach ($rows as $row) {
            $payload = $map($row);
            $id = $payload['id'];
            unset($payload['id']);
            $target->table($targetTable)->updateOrInsert(['id' => $id], $payload);
        }
        $report->addWritten($targetTable, $rows->count());
    }
}
