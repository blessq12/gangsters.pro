<?php

namespace App\Infrastructure\Company\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class CMP_Branch extends Model
{
    protected $table = 'CMP_branches';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'name',
        'is_active',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'company_id' => 'integer',
        'is_active' => 'boolean',
    ];

    public function kitchens(): HasMany
    {
        return $this->hasMany(CMP_Kitchen::class, 'branch_id');
    }
}
