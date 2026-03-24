<?php

namespace App\Services\Slug;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class TransliteratingSlugGenerator
{
    /**
     * @param  class-string<Model>  $modelClass
     */
    public function uniqueFrom(string $source, string $modelClass, ?int $ignoreId = null): string
    {
        if (!is_subclass_of($modelClass, Model::class)) {
            throw new InvalidArgumentException('Model class must be an Eloquent model.');
        }

        $base = Str::slug($source);
        if ($base === '') {
            $base = 'item';
        }

        $slug = $base;
        $suffix = 2;

        while ($this->exists($modelClass, $slug, $ignoreId)) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    private function exists(string $modelClass, string $slug, ?int $ignoreId): bool
    {
        $query = $modelClass::query()->where('slug', $slug);

        if ($ignoreId !== null) {
            $query->whereKeyNot($ignoreId);
        }

        return $query->exists();
    }
}

