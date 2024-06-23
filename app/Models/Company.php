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
}
