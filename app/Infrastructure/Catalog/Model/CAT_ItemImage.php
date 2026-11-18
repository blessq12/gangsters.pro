<?php

namespace App\Infrastructure\Catalog\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CAT_ItemImage extends Model
{
    protected $table = 'CAT_item_images';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'item_id',
        'path',
        'disk',
        'sort_order',
        'focus',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'item_id' => 'integer',
        'sort_order' => 'integer',
        'focus' => 'array',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(CAT_Item::class, 'item_id');
    }
}
