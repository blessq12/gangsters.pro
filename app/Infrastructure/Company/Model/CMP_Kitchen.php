<?php

namespace App\Infrastructure\Company\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CMP_Kitchen extends Model
{
    protected $table = 'CMP_kitchens';

    protected $fillable = [
        'branch_id',
        'city',
        'street',
        'house',
        'comment',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'branch_id' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(CMP_Branch::class, 'branch_id');
    }
}
