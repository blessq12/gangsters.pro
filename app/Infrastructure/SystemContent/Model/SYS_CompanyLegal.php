<?php

namespace App\Infrastructure\SystemContent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SYS_CompanyLegal extends Model
{
    use HasFactory;

    protected $table = 'company_legals';

    protected $guarded = [];

    public function company(): BelongsTo
    {
        return $this->belongsTo(SYS_Company::class, 'company_id');
    }
}

