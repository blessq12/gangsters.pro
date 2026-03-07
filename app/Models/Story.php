<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Story extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
        'visible',
        'non_hide',
        'start_time',
        'end_time',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
