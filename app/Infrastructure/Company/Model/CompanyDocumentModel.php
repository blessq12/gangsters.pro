<?php

namespace App\Infrastructure\Company\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $company_id
 * @property string $slug
 * @property string $name
 * @property string $content
 * @property string $path
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
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
