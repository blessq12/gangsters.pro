<?php

namespace App\Infrastructure\Order\Model;

use Illuminate\Database\Eloquent\Model;

final class PRM_Zone extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'PRM_zones';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'branch_id',
        'name',
        'fee_kopecks',
        'is_remote',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'branch_id' => 'integer',
        'fee_kopecks' => 'integer',
        'is_remote' => 'boolean',
    ];
}
