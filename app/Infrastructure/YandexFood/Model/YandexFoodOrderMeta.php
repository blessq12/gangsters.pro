<?php

namespace App\Infrastructure\YandexFood\Model;

use Illuminate\Database\Eloquent\Model;

final class YandexFoodOrderMeta extends Model
{
    protected $table = 'yandex_food_order_meta';

    protected $fillable = [
        'order_id',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
