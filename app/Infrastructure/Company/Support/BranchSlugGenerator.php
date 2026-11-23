<?php

namespace App\Infrastructure\Company\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * Slug уникален в пределах филиала. Если базовый slug занят —
 * добавляется суффикс филиала (-b{id}), затем числовой счётчик.
 */
final class BranchSlugGenerator
{
    /**
     * @param  Builder<*>  $branchScope  запрос, уже ограниченный филиалом
     */
    public function generate(Builder $branchScope, string $name, int $branchId, string $fallback): string
    {
        $base = Str::slug($name);
        if ($base === '') {
            $base = $fallback;
        }

        if (! $this->isTaken($branchScope, $base)) {
            return $base;
        }

        $withBranch = $base.'-b'.$branchId;
        if (! $this->isTaken($branchScope, $withBranch)) {
            return $withBranch;
        }

        $counter = 2;
        do {
            $candidate = $withBranch.'-'.$counter;
            $counter++;
        } while ($this->isTaken($branchScope, $candidate));

        return $candidate;
    }

    /**
     * @param  Builder<*>  $branchScope
     */
    public function isTaken(Builder $branchScope, string $slug, ?int $ignoreId = null): bool
    {
        return (clone $branchScope)
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn (Builder $query) => $query->where('id', '!=', $ignoreId))
            ->exists();
    }
}
