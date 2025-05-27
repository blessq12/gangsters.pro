<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;
    use HasFactory;
    /**
     * Order statuses [1, 10, 11]
     *    1 - Новый
     *    11 - Списан
     *    10 - Выполнен
     */

    const STATUS_NEW = 1;
    const STATUS_PAID = 10;
    const STATUS_CANCELLED = 11;
    const GET_STATUS = [
        1 => 'Новый',
        10 => 'Выполнен',
        11 => 'Списан'
    ];


    protected $casts = [
        'created_at' => 'datetime',
        'promos' => 'array',
    ];

    protected $fillable = [
        'discriminator',
        'name',
        'tel',
        'full_address',
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
        'comment',
        'eatsId',
        'restaurantId',
        'latitude',
        'longitude',
        'deliveryDate',
        'deliveryType',
        'itemsCost',
        'deliveryFee',
        'change',
        'promos',
        'deliveryAddress'
    ];

    protected $with = ['items'];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
