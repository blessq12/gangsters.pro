<?php

namespace App\Infrastructure\Company\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class CMP_BranchSet extends Model
{
    protected $table = 'CMP_branch_sets';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'branch_id',
        'name',
        'slug',
        'description',
        'is_available',
        'price_kopecks',
        'status',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'branch_id' => 'integer',
        'is_available' => 'boolean',
        'price_kopecks' => 'integer',
    ];

    /**
     * @return HasMany<CMP_BranchSetLine, $this>
     */
    public function lines(): HasMany
    {
        return $this->hasMany(CMP_BranchSetLine::class, 'set_id')->orderBy('id');
    }
}
