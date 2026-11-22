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
        'branch_id',
        'gift_product_id',
        'gift_rules',
        'delivery_benefit',
        'complement_set_benefit',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'branch_id' => 'integer',
        'gift_product_id' => 'integer',
        'gift_rules' => 'array',
        'delivery_benefit' => 'array',
        'complement_set_benefit' => 'array',
    ];
}
