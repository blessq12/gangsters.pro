<?php

namespace App\Infrastructure\Company\Model;

use Illuminate\Database\Eloquent\Model;

final class BranchModel extends Model
{
    protected $table = 'CMP_branches';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'name',
        'is_active',
        'schedule',
        'city',
        'street',
        'house',
        'comment',
        'latitude',
        'longitude',
        'phone',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'company_id' => 'integer',
        'is_active' => 'boolean',
        'schedule' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
    ];
}
