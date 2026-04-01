<?php

namespace App\Infrastructure\Product\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class PRD_Tag extends Model
{
    use HasFactory;

    protected $table = 'PRD_tags';

    protected $fillable = [
        'code',
        'label',
        'color',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'bool',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $model): void {
            if ($model->isDirty('label') || !$model->code) {
                $model->code = static::makeUniqueCodeFromLabel($model->label, $model->id);
            }
        });
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            PRD_Product::class,
            'PRD_product_tag',
            'tag_id',
            'product_id',
        )->withTimestamps();
    }

    private static function makeUniqueCodeFromLabel(?string $label, ?int $ignoreId = null): string
    {
        $base = Str::slug((string) $label, '-');
        if ($base === '') {
            $base = 'tag';
        }

        $code = $base;
        $suffix = 2;
        while (static::query()
            ->where('code', $code)
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->exists()) {
            $code = $base . '-' . $suffix;
            $suffix++;
        }

        return $code;
    }
}
