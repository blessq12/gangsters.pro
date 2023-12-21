<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'consist',
        'weight',
        'price'
    ];
    public function images(){
        return $this->morphMany(Image::class,'image');
    }
    public function productCategory(){
        return $this->belongsTo(ProductCategory::class);
    }
}
