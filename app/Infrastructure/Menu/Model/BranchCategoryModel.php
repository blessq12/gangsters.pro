<?php

namespace App\Infrastructure\Menu\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $branch_id
 * @property string $name
 * @property string $slug
 * @property int $sort_order
 * @property bool $is_active
 * @property bool $is_accompanying
 * @property bool $is_complement
 * @property bool $is_gift
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class BranchCategoryModel extends Model
{
    protected $table = 'CMP_branch_categories';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'branch_id',
        'name',
        'slug',
        'sort_order',
        'is_active',
        'is_accompanying',
        'is_complement',
        'is_gift',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'branch_id' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'is_accompanying' => 'boolean',
        'is_complement' => 'boolean',
        'is_gift' => 'boolean',
    ];

    /**
     * @return HasMany<BranchCategoryItemModel, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(BranchCategoryItemModel::class, 'category_id');
    }
}
