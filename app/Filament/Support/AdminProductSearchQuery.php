<?php

namespace App\Filament\Support;

use App\Domain\Product\Entity\Product as ProductEntity;
use App\Infrastructure\Product\Model\PRD_Product;
use Illuminate\Database\Eloquent\Builder;

final class AdminProductSearchQuery
{
    /**
     * @return array<int, string> id => label
     */
    public function optionsForSelect(?string $search = null, int $limit = 50): array
    {
        $query = PRD_Product::query()
            ->where('status', ProductEntity::STATUS_ACTIVE)
            ->orderBy('name')
            ->limit($limit);

        if (filled($search)) {
            $term = '%'.$search.'%';
            $query->where(function (Builder $builder) use ($term): void {
                $builder
                    ->where('name', 'like', $term)
                    ->orWhere('articul', 'like', $term);
            });
        }

        return $query
            ->get(['id', 'name', 'articul'])
            ->mapWithKeys(fn (PRD_Product $product): array => [
                (int) $product->id => trim((string) $product->name)
                    .(filled($product->articul) ? ' ('.$product->articul.')' : ''),
            ])
            ->all();
    }
}
