<?php

namespace App\Infrastructure\SystemContent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class SYS_Company extends Model
{
    use HasFactory;

    protected $table = 'companies';

    protected $fillable = [
        'name',
        'brand_name',
        'description',
        'tagline',
        'country',
        'state',
        'city',
        'street',
        'house',
        'address_comment',
        'phone',
        'phone_additional',
        'support_phone',
        'whatsapp_phone',
        'email_address',
        'public_email',
        'telegram',
        'site_url',
        'work_hours',
        'delivery_hours',
        'work_schedule',
        'min_order_amount_kopecks',
        'delivery_fee_kopecks',
        'average_delivery_time_minutes',
        'city_coverage',
        'vk',
        'inst',
        'logo',
    ];

    protected $casts = [
        'work_schedule' => 'array',
    ];

    public function legal(): HasOne
    {
        return $this->hasOne(SYS_CompanyLegal::class, 'company_id');
    }
}
