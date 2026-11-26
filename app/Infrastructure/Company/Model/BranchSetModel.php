<?php

namespace App\Infrastructure\Company\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $branch_id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property bool $is_available
 * @property int $price_kopecks
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class BranchSetModel extends Model
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
     * @return HasMany<BranchSetLineModel, $this>
     */
    public function lines(): HasMany
    {
        return $this->hasMany(BranchSetLineModel::class, 'set_id')->orderBy('id');
    }
}
