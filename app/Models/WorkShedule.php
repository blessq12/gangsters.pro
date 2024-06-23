<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkShedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'day',
        'open_time',
        'close_time',
        'day_off',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
