<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'frontpad_api_key',
        'use_coin_system',
        'coin_system_percent',
        'use_discount_system',
        'discount_system_percent',
    ];
}
