<?php

namespace App\Infrastructure\Order\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $branch_id
 * @property array $gift_rules
 * @property array $delivery_benefit
 * @property array $complement_set_benefit
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class BranchCommercialRulesModel extends Model
{
    protected $table = 'ORD_branch_commercial_rules';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'branch_id',
        'gift_rules',
        'delivery_benefit',
        'complement_set_benefit',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'branch_id' => 'integer',
        'gift_rules' => 'array',
        'delivery_benefit' => 'array',
        'complement_set_benefit' => 'array',
    ];
}
