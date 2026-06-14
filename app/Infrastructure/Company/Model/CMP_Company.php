<?php

namespace App\Infrastructure\Company\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CMP_Company extends Model
{
    protected $table = 'CMP_company';

    protected $fillable = [
        'name',
        'brand_name',
        'description',
        'tagline',
        'phone',
        'phone_additional',
        'support_phone',
        'whatsapp_phone',
        'email_address',
        'public_email',
        'work_hours',
        'work_schedule',
        'logo',
        'telegram',
        'site_url',
        'vk',
        'inst',
    ];

    protected $casts = [
        'work_schedule' => 'array',
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
