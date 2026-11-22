<?php

namespace App\Infrastructure\Company\Model;

use Illuminate\Database\Eloquent\Model;

final class CMP_BranchTag extends Model
{
    protected $table = 'CMP_branch_tags';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'branch_id',
        'code',
        'label',
        'color',
        'is_active',
        'sort_order',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'branch_id' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
