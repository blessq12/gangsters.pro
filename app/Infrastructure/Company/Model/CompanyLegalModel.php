<?php

namespace App\Infrastructure\Company\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $company_id
 * @property string $full_name
 * @property string $inn
 * @property string $ogrn
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class CompanyLegalModel extends Model
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
