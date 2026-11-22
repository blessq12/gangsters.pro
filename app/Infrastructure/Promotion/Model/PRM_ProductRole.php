<?php

namespace App\Infrastructure\Promotion\Model;

use Illuminate\Database\Eloquent\Model;

final class PRM_ProductRole extends Model
{
    protected $table = 'PRM_product_roles';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'branch_id',
        'catalog_item_id',
        'role',
        'name_snapshot',
        'price_kopecks_snapshot',
        'paid_twin_catalog_item_id',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'branch_id' => 'integer',
        'catalog_item_id' => 'integer',
        'price_kopecks_snapshot' => 'integer',
        'paid_twin_catalog_item_id' => 'integer',
    ];
}
