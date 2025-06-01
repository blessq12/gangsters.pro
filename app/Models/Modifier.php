<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modifier extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'name',
        'price',
        'vat',
        'min_amount',
        'max_amount',
        'quantity',
        'visible'
    ];

    public function group()
    {
        return $this->belongsTo(ModifierGroup::class, 'group_id');
    }
}
