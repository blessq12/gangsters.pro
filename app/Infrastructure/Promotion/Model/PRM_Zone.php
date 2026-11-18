<?php

namespace App\Infrastructure\Promotion\Model;

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
        'name',
        'fee_kopecks',
        'is_remote',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'fee_kopecks' => 'integer',
        'is_remote' => 'boolean',
    ];
}
