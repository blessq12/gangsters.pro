<?php

namespace App\Infrastructure\Company\Model;

use App\Domain\Supporting\Company\Enum\BranchMenuEntryKind;
use Illuminate\Database\Eloquent\Model;

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
