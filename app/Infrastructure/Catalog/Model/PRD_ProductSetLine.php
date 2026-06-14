<?php

namespace App\Infrastructure\Catalog\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PRD_ProductSetLine extends Model
{
    protected $table = 'PRD_product_set_lines';

    protected $fillable = [
        'set_id',
        'product_id',
        'quantity',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $line): void {
            if (! $line->set_id) {
                return;
            }

            $maxSortOrder = self::query()
                ->where('set_id', $line->set_id)
                ->max('sort_order');

            $line->sort_order = is_null($maxSortOrder) ? 0 : $maxSortOrder + 1;
        });
    }

    public function set(): BelongsTo
    {
        return $this->belongsTo(PRD_Product::class, 'set_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(PRD_Product::class, 'product_id');
    }
}
