<?php

namespace App\Infrastructure\Catalog\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CAT_Price extends Model
{
    protected $table = 'CAT_prices';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'item_id',
        'type',
        'amount_kopecks',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'item_id' => 'integer',
        'amount_kopecks' => 'integer',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(CAT_Item::class, 'item_id');
    }
}
