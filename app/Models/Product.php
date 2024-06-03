<?php

namespace App\Models;

// Traits
use Illuminate\Database\Eloquent\Factories\HasFactory;
use BinaryCats\Sku\Concerns\SkuOptions;

use Illuminate\Database\Eloquent\Model;
use PDO;

class Product extends Model
{
    use HasFactory;
    // use \BinaryCats\Sku\HasSku;
    use \Encore\Admin\Traits\Resizable;

    protected $fillable = [
        'name',
        'consist',
        'weight',
        'price',
        'product_category_id',
        'visible',
        'sku'
    ];

    public function skuOptions(): SkuOptions
    {
        return SkuOptions::make()
            ->from(['name'])
            ->target('sku')
            ->using('_')
            ->forceUnique(true)
            ->generateOnCreate(true)
            ->refreshOnUpdate(false);
    }

    public function imgs()
    {
        return $this->hasMany(ProductImage::class, 'product_id');
    }
}
