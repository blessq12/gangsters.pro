<?php

namespace App\Models;

// Traits
use Illuminate\Database\Eloquent\Factories\HasFactory;
use BinaryCats\Sku\Concerns\SkuOptions;
use Illuminate\Database\Eloquent\Model;


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
        'visible',
        'sku'
    ];

    public static function boot()
    {
        parent::boot();
        static::created(function ($product) {
            $product->order = Product::max('order') + 1;
            $product->save();
        });
    }

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

    public function images()
    {
        return $this->imgs->map(function ($image) {
            return '/uploads/' . $image->path;
        });
    }
    public function thumbs()
    {
        //
        return $this->imgs->map(function ($image) {
            if (!$image) return null;
            $path = explode('.', $image->path);
            return [
                'small' => '/uploads/' . $path[0] . '-sm.' . $path[1],
                'medium' => '/uploads/' . $path[0] . '-md.' . $path[1],
                'large' => '/uploads/' . $path[0] . '-lg.' . $path[1],
            ];
        });
    }
    public function getThumbsAttribute()
    {
        return $this->thumbs();
    }
    public function getImagesAttribute()
    {
        return $this->images();
    }

    public function categories()
    {
        return $this->belongsToMany(ProductCategory::class, 'category_product', 'product_id', 'category_id')
            ->withPivot('order');
    }
}
