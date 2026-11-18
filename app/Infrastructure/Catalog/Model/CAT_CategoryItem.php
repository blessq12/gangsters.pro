<?php

namespace App\Infrastructure\Catalog\Model;

use Illuminate\Database\Eloquent\Model;

final class CAT_CategoryItem extends Model
{
    public $timestamps = false;

    protected $table = 'CAT_category_items';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'category_id',
        'item_id',
        'sort_order',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'category_id' => 'integer',
        'item_id' => 'integer',
        'sort_order' => 'integer',
    ];
}
