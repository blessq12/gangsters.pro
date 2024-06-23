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

    public static function getCurrentDayShedule()
    {
        $currentDay = strtolower(now()->format('l'));
        return self::where('day_eng', $currentDay)->first();
    }

    public function nextDayOpenTime()
    {
        return self::where('day_eng', now()->addDay()->format('l'))->first()->open_time;
    }
}
