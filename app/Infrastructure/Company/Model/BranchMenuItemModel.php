<?php

namespace App\Infrastructure\Company\Model;

use Illuminate\Database\Eloquent\Model;

final class BranchMenuItemModel extends Model
{
    protected $table = 'CMP_branch_menu_items';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'branch_id',
        'catalog_item_id',
        'is_available',
        'price_kopecks',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'branch_id' => 'integer',
        'catalog_item_id' => 'integer',
        'is_available' => 'boolean',
        'price_kopecks' => 'integer',
    ];
}
