<?php

namespace App\Infrastructure\Menu\Model;

use App\Domain\Supporting\Menu\Enum\BranchMenuEntryKind;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $category_id
 * @property string $entry_kind
 * @property int $entry_id
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class BranchCategoryItemModel extends Model
{
    public $timestamps = false;

    protected $table = 'CMP_branch_category_items';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'category_id',
        'entry_kind',
        'entry_id',
        'sort_order',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'category_id' => 'integer',
        'entry_kind' => BranchMenuEntryKind::class,
        'entry_id' => 'integer',
        'sort_order' => 'integer',
    ];
}
