<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;
    protected $hidden = [
        'image_type',
        'created_at',
        'updated_at',
        'image_id',
        'type'
    ];
    protected $fillable = [
        'path',
        'type',
        'uniq'
    ];
}
