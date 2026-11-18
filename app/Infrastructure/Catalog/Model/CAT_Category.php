<?php

namespace App\Infrastructure\Catalog\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class CAT_Category extends Model
{
    protected $table = 'CAT_categories';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'sort_order',
        'is_active',
        'is_accompanying',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'is_accompanying' => 'boolean',
    ];

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(
            CAT_Item::class,
            'CAT_category_items',
            'category_id',
            'item_id',
        )->withPivot('sort_order');
    }
}
