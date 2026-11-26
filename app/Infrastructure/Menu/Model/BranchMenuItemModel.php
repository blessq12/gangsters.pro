<?php

namespace App\Infrastructure\Menu\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $branch_id
 * @property int $catalog_item_id
 * @property bool $is_available
 * @property int $price_kopecks
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
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
