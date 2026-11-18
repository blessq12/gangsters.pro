<?php

namespace App\Infrastructure\Promotion\Model;

use Illuminate\Database\Eloquent\Model;

final class PRM_Policy extends Model
{
    protected $table = 'PRM_policies';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'gift_rules',
        'delivery_benefit',
        'complement_set_benefit',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'gift_rules' => 'array',
        'delivery_benefit' => 'array',
        'complement_set_benefit' => 'array',
    ];
}
