<?php

namespace App\Infrastructure\Content\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CMP_CompanyDocument extends Model
{
    protected $table = 'CMP_company_documents';

    protected $fillable = [
        'company_id',
        'key',
        'title',
        'content',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CMP_Company::class, 'company_id');
    }
}
