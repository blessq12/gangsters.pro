<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    /**
     * Таблица системных акций/промо.
     */
    protected $table = 'promotions';

    protected $fillable = [
        'title',
        'description',
        'image',
    ];
}

