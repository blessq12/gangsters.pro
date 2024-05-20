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
        return $this->hasMany(Product::class)->where('visible', true);
    }
}
