<?php

namespace App\Infrastructure\Category\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PRD_Category extends Model
{
    use HasFactory;

    protected $table = 'PRD_categories';

    protected $fillable = [
        'name',
        'slug',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'bool',
    ];

    public function links(): HasMany
    {
        return $this->hasMany(PRD_CategoryProduct::class, 'category_id');
    }
}

