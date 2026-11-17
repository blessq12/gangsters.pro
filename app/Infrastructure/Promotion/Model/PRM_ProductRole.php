<?php

namespace App\Infrastructure\Promotion\Model;

use Illuminate\Database\Eloquent\Model;

class PRM_ProductRole extends Model
{
    protected $table = 'PRM_product_roles';

    protected $fillable = [
        'product_id',
        'role',
        'name',
        'price_kopecks',
        'paid_twin_product_id',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'price_kopecks' => 'integer',
        'paid_twin_product_id' => 'integer',
    ];
}
