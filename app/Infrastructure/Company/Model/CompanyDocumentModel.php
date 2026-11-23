<?php

namespace App\Infrastructure\Company\Model;

use Illuminate\Database\Eloquent\Model;

final class CompanyDocumentModel extends Model
{
    protected $table = 'CMP_company_documents';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'slug',
        'name',
        'content',
        'path',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'company_id' => 'integer',
    ];
}
