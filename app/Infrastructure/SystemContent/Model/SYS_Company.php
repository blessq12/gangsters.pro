<?php

namespace App\Infrastructure\SystemContent\Model;

use App\Infrastructure\SystemContent\Model\SYS_CompanyLegal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class SYS_Company extends Model
{
    use HasFactory;

    protected $table = 'companies';

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

    public function legal(): HasOne
    {
        return $this->hasOne(SYS_CompanyLegal::class, 'company_id');
    }
}

