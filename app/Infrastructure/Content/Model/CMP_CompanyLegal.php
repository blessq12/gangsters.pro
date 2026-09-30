<?php

namespace App\Infrastructure\Content\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CMP_CompanyLegal extends Model
{
    protected $table = 'CMP_company_legal';

    protected $fillable = [
        'company_id',
        'full_name',
        'inn',
        'ogrn',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CMP_Company::class, 'company_id');
    }
}
