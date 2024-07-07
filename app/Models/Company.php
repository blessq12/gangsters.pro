<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'country',
        'state',
        'city',
        'street',
        'house',
        'phone',
        'phone_additional',
        'email_address',
        'logo',
    ];

    public function legals()
    {
        return $this->hasOne(CompanyLegal::class);
    }

    public function workShedules()
    {
        return $this->hasMany(WorkShedule::class);
    }

    public function currentDayShedule()
    {
        return $this->workShedules()->where('day_eng', now()->format('l'))->first();
    }

    public function frontShedules()
    {
        $shedules = $this->workShedules()->get();

        foreach ($shedules as $shedule) {
            $shedule->open_time = \Carbon\Carbon::parse($shedule->open_time)->format('Y-m-d H:i:s');
            $shedule->close_time = \Carbon\Carbon::parse($shedule->close_time)->format('Y-m-d H:i:s');
        }

        return $shedules;
    }
}
