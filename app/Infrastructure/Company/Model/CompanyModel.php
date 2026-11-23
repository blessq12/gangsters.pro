<?php

namespace App\Infrastructure\Company\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class CompanyModel extends Model
{
    protected $table = 'CMP_companies';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'phone',
        'email',
        'socials',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'socials' => 'array',
    ];

    public function legal(): HasOne
    {
        return $this->hasOne(CompanyLegalModel::class, 'company_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(CompanyDocumentModel::class, 'company_id');
    }

    public function branches(): HasMany
    {
        return $this->hasMany(BranchModel::class, 'company_id');
    }
}
