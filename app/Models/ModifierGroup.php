<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModifierGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'min_selected_modifiers',
        'max_selected_modifiers',
        'sort_order'
    ];

    public function modifiers()
    {
        return $this->hasMany(Modifier::class, 'group_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_modifier_groups', 'group_id', 'product_id');
    }
}
