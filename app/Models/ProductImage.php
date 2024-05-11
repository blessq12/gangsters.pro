<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \Encore\Admin\Traits\Resizable;

class ProductImage extends Model
{
    use HasFactory, Resizable;

    protected $fillable = [
        'product_id',
        'name',
        'path'
    ];
}
