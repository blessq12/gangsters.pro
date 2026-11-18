<?php

namespace App\Infrastructure\Company\Model;

use Illuminate\Database\Eloquent\Model;

final class CMP_Kitchen extends Model
{
    protected $table = 'CMP_kitchens';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'branch_id',
        'city',
        'street',
        'house',
        'comment',
        'latitude',
        'longitude',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'branch_id' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
    ];
}
