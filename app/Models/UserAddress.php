<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'street',
        'house',
        'building',
        'staircase',
        'floor',
        'apartment',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
