<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'tel',
        'street',
        'house',
        'building',
        'staircase',
        'floor',
        'apartment',
        'total',
        'delivery',
        'user_id',
        'patType',
        'personQty',
        'comment'
    ];
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
