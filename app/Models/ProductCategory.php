<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    use HasFactory;
    protected $fillable = [
        'uri',
        'name'
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'category_product', 'category_id', 'product_id')
            ->where('visible', true)->where('price', '!=', null)
            ->where('weight', '!=', null)
            ->withPivot('order');
    }
}
