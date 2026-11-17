<?php

namespace App\Infrastructure\Company\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CMP_Branch extends Model
{
    protected $table = 'CMP_branches';

    protected $fillable = [
        'company_id',
        'name',
        'is_active',
    ];

    protected $casts = [
        'company_id' => 'integer',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CMP_Company::class, 'company_id');
    }

    public function kitchens(): HasMany
    {
        return $this->hasMany(CMP_Kitchen::class, 'branch_id');
    }
}
