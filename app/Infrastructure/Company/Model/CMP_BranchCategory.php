<?php

namespace App\Infrastructure\Company\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class CMP_BranchCategory extends Model
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
     * @return HasMany<CMP_BranchCategoryItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(CMP_BranchCategoryItem::class, 'category_id');
    }
}
