<?php

namespace App\Filament\Support;

use App\Domain\Product\Entity\Product as ProductEntity;
use App\Infrastructure\Product\Model\PRD_Product;
use App\Support\Money;
use App\Support\Product\ProductStatusLabels;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

final class AdminProductTableQuery
{
    public function paginate(
        ?string $search,
        ?string $status,
        int $page,
        int $perPage,
        string $pageName,
        ?bool $countsAsRoll = null,
        ?bool $giftCandidate = null,
        ?bool $isComplementSet = null,
    ): LengthAwarePaginator {
        $query = PRD_Product::query()->orderByDesc('updated_at');

        if ($status === ProductEntity::STATUS_ACTIVE) {
            $query->where('status', ProductEntity::STATUS_ACTIVE);
        } elseif ($status === ProductEntity::STATUS_ARCHIVED) {
            $query->where('status', ProductEntity::STATUS_ARCHIVED);
        }

        if ($countsAsRoll !== null) {
            $query->where('cart_rule_counts_as_roll', $countsAsRoll);
        }

        if ($giftCandidate !== null) {
            $query->where('cart_rule_gift_candidate', $giftCandidate);
        }

        if ($isComplementSet !== null) {
            $query->where('cart_rule_is_complement_set', $isComplementSet);
        }

        if (filled($search)) {
            $term = '%'.$search.'%';
            $query->where(function (Builder $builder) use ($term): void {
                $builder
                    ->where('name', 'like', $term)
                    ->orWhere('articul', 'like', $term);
            });
        }

        $paginator = $query->paginate(perPage: $perPage, page: $page);

        $items = collect($paginator->items())
            ->map(fn (PRD_Product $product): array => $this->presentListItem($product))
            ->keyBy('id');

        return new LengthAwarePaginator(
            $items,
            $paginator->total(),
            $perPage,
            max(1, $page),
            ['path' => request()->url(), 'pageName' => $pageName],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function presentListItem(PRD_Product $product): array
    {
        $status = (string) $product->status;

        return [
            'id' => (int) $product->id,
            'name' => (string) $product->name,
            'articul' => $product->articul,
            'status' => $status,
            'status_label' => ProductStatusLabels::label($status),
            'price_rubles' => $product->price !== null
                ? Money::kopecksToApiRubles((int) $product->price)
                : null,
            'updated_at' => $product->updated_at?->toIso8601String() ?? '',
            'counts_as_roll' => (bool) $product->cart_rule_counts_as_roll,
            'gift_candidate' => (bool) $product->cart_rule_gift_candidate,
            'is_complement_set' => (bool) $product->cart_rule_is_complement_set,
        ];
    }
}
