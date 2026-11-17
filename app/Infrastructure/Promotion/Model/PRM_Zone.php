<?php

namespace App\Infrastructure\Promotion\Model;

use Illuminate\Database\Eloquent\Model;

class PRM_Zone extends Model
{
    protected $table = 'PRM_zones';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'fee_kopecks',
        'is_remote',
    ];

    protected $casts = [
        'fee_kopecks' => 'integer',
        'is_remote' => 'boolean',
    ];
}
