<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{

    /**
     * Order statuses [1, 10, 11]
     *    1 - Новый
     *    11 - Списан
     *    10 - Выполнен
     */

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
