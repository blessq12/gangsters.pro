<?php

namespace Database\Seeders;

use App\Domain\Supporting\Catalog\Enum\PriceType;
use App\Domain\Supporting\Catalog\Enum\ProductStatus;
use App\Infrastructure\Catalog\Model\CAT_Item;
use App\Infrastructure\Catalog\Model\CAT_Price;
use App\Infrastructure\Company\Model\CMP_Branch;
use App\Infrastructure\Company\Model\CMP_BranchMenuItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Меню филиала: все active позиции каталога с regular-ценой на каждый активный филиал.
 */
class BranchMenuSeeder extends Seeder
{
    public function run(): void
    {
        $branchIds = CMP_Branch::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->pluck('id')
            ->all();

        if ($branchIds === []) {
            throw new RuntimeException('Нет активных филиалов: сначала запустите CompanySeeder.');
        }

        $items = CAT_Item::query()
            ->where('status', ProductStatus::Active->value)
            ->orderBy('id')
            ->get(['id']);

        if ($items->isEmpty()) {
            throw new RuntimeException('Нет позиций каталога: сначала запустите CatalogSeeder.');
        }

        $prices = CAT_Price::query()
            ->where('type', PriceType::Regular->value)
            ->whereIn('item_id', $items->pluck('id'))
            ->get()
            ->keyBy('item_id');

        DB::transaction(function () use ($branchIds, $items, $prices): void {
            foreach ($branchIds as $branchId) {
                foreach ($items as $item) {
                    $price = $prices->get($item->id);
                    $priceKopecks = $price instanceof CAT_Price ? (int) $price->amount_kopecks : 0;

                    CMP_BranchMenuItem::query()->updateOrCreate(
                        [
                            'branch_id' => $branchId,
                            'catalog_item_id' => $item->id,
                        ],
                        [
                            'is_available' => true,
                            'price_kopecks' => $priceKopecks,
                        ],
                    );
                }
            }
        });
    }
}
