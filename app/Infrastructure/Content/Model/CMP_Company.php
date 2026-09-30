<?php

namespace App\Infrastructure\Content\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CMP_Company extends Model
{
    protected $table = 'CMP_company';

    protected $fillable = [
        'name',
        'description',
        'phone',
        'email',
        'socials',
        'schedule',
    ];

    protected $casts = [
        'socials' => 'array',
        'schedule' => 'array',
    ];

    public function legal(): HasOne
    {
        return $this->hasOne(CMP_CompanyLegal::class, 'company_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(CMP_CompanyDocument::class, 'company_id');
    }
}
