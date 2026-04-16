<?php

namespace App\Support\Slug;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class UniqueSlugGenerator
{
    /**
     * @param  class-string<Model>  $modelClass
     */
    public function uniqueFrom(string $value, string $modelClass, int|string|null $ignoreId = null): string
    {
        $base = Str::slug($value, '-');
        if ($base === '') {
            $base = 'item';
        }

        $slug = $base;
        $suffix = 2;

        while ($modelClass::query()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn ($q) => $q->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
