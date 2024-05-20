<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PDO;

class Product extends Model
{
    use HasFactory;
    use \Encore\Admin\Traits\Resizable;

    protected $fillable = [
        'name',
        'consist',
        'weight',
        'price',
        'product_category_id',
        'visible'
    ];

    public function imgs()
    {
        return $this->hasMany(ProductImage::class, 'product_id');
    }
}
