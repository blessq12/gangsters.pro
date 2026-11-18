<?php

namespace App\Infrastructure\Company\Model;

use Illuminate\Database\Eloquent\Model;

final class CMP_CompanyLegal extends Model
{
    protected $table = 'CMP_company_legal';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'full_name',
        'inn',
        'ogrn',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'company_id' => 'integer',
    ];
}
