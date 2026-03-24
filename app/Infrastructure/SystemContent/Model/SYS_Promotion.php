<?php

namespace App\Infrastructure\SystemContent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class SYS_Promotion extends Model
{
    use HasFactory;

    protected $table = 'promotions';

    protected $fillable = [
        'title',
        'description',
        'image',
    ];
}

