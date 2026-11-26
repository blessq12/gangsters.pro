<?php

namespace App\Infrastructure\Menu\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $branch_id
 * @property string $code
 * @property string $label
 * @property string $color
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class BranchTagModel extends Model
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
