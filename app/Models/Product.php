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
        'price',
        'product_category_id',
        'visible'
    ];

    public function images(){
        return $this->morphMany(Image::class, 'image');
    }
    public function originalImage(){
        return $this->images()->where('type', 'original');
    }
    public function thumbMedium(){
        return $this->images()->where('type', 'thumb-medium');
    }
    public function thumbSmall(){
        return $this->images()->where('type', 'thumb-small');
    }

    public function productCategory(){
        return $this->belongsTo(ProductCategory::class);
    }
}
