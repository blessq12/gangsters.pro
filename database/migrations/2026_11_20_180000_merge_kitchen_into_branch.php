<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Слияние Kitchen в Branch.
 *
 * Для уже мигрированных БД: адрес/координаты переезжают в CMP_branches
 * (первая кухня на филиал), CMP_kitchens удаляется, а ссылки kitchen_id /
 * company_kitchen_id в других BC превращаются в branch_id (с пересчётом id кухни → id филиала).
 * На greenfield-схеме (create-миграции уже без кухни) — no-op.
 * Идемпотентно: каждый шаг защищён Schema::hasTable / Schema::hasColumn.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->addBranchAddressColumns();

        $kitchenToBranch = $this->loadKitchenToBranchMap();
        $this->copyKitchenAddressToBranches();

        // Ссылки считаем ДО удаления CMP_kitchens: нужен маппинг kitchen.id → kitchen.branch_id.
        $this->moveReferenceToBranch('ORD_orders', 'kitchen_id', $kitchenToBranch, true);
        $this->moveReferenceToBranch('KIT_tickets', 'kitchen_id', $kitchenToBranch, false);
        $this->moveReferenceToBranch('DLV_configurations', 'company_kitchen_id', $kitchenToBranch, true);
        $this->moveReferenceToBranch('WH_stock_writeoffs', 'kitchen_id', $kitchenToBranch, true);

        Schema::dropIfExists('CMP_kitchens');
    }

    public function down(): void
    {
        // Необратимо: кухня как отдельная сущность упразднена, адрес живёт в CMP_branches.
    }

    private function addBranchAddressColumns(): void
    {
        if (! Schema::hasTable('CMP_branches')) {
            return;
        }

        Schema::table('CMP_branches', function (Blueprint $table): void {
            foreach (['city', 'street', 'house', 'comment'] as $column) {
                if (! Schema::hasColumn('CMP_branches', $column)) {
                    $table->string($column)->nullable();
                }
            }

            foreach (['latitude', 'longitude'] as $column) {
                if (! Schema::hasColumn('CMP_branches', $column)) {
                    $table->decimal($column, 10, 7)->nullable();
                }
            }
        });
    }

    /**
     * @return array<int, int> kitchen.id => kitchen.branch_id
     */
    private function loadKitchenToBranchMap(): array
    {
        if (! Schema::hasTable('CMP_kitchens')) {
            return [];
        }

        $map = [];
        foreach (DB::table('CMP_kitchens')->select(['id', 'branch_id'])->get() as $kitchen) {
            $map[(int) $kitchen->id] = (int) $kitchen->branch_id;
        }

        return $map;
    }

    /**
     * Адрес берётся у первой кухни филиала; уже заполненные поля филиала не перетираем.
     */
    private function copyKitchenAddressToBranches(): void
    {
        if (! Schema::hasTable('CMP_kitchens') || ! Schema::hasTable('CMP_branches')) {
            return;
        }

        $fields = ['city', 'street', 'house', 'comment', 'latitude', 'longitude'];
        $copied = [];

        foreach (DB::table('CMP_kitchens')->orderBy('id')->get() as $kitchen) {
            $branchId = (int) $kitchen->branch_id;
            if (isset($copied[$branchId])) {
                continue;
            }
            $copied[$branchId] = true;

            $branch = DB::table('CMP_branches')->where('id', $branchId)->first();
            if ($branch === null) {
                continue;
            }

            $patch = [];
            foreach ($fields as $field) {
                if ($branch->{$field} === null && $kitchen->{$field} !== null) {
                    $patch[$field] = $kitchen->{$field};
                }
            }

            if ($patch !== []) {
                DB::table('CMP_branches')->where('id', $branchId)->update($patch);
            }
        }
    }

    /**
     * Переносит ссылку на кухню в branch_id и удаляет старую колонку.
     *
     * @param  array<int, int>  $kitchenToBranch
     */
    private function moveReferenceToBranch(
        string $tableName,
        string $oldColumn,
        array $kitchenToBranch,
        bool $nullable,
    ): void {
        if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, $oldColumn)) {
            return;
        }

        if (! Schema::hasColumn($tableName, 'branch_id')) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->unsignedBigInteger('branch_id')->nullable()->index();
            });
        }

        $kitchenIds = DB::table($tableName)
            ->whereNotNull($oldColumn)
            ->whereNull('branch_id')
            ->distinct()
            ->pluck($oldColumn);

        foreach ($kitchenIds as $kitchenId) {
            $kitchenId = (int) $kitchenId;
            DB::table($tableName)
                ->where($oldColumn, $kitchenId)
                ->whereNull('branch_id')
                ->update(['branch_id' => $kitchenToBranch[$kitchenId] ?? $kitchenId]);
        }

        Schema::table($tableName, function (Blueprint $table) use ($oldColumn): void {
            $table->dropColumn($oldColumn);
        });

        if (! $nullable) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->unsignedBigInteger('branch_id')->nullable(false)->change();
            });
        }
    }
};
