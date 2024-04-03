<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    public function legals()
    {
        return $this->hasOne(CompanyLegal::class);
    }

    public function logo()
    {
        return $this->morphOne(Image::class, 'image')->where('type', 'app-logo');
    }
}
