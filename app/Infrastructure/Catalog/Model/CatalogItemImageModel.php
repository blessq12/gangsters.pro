<?php

namespace App\Infrastructure\Catalog\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $item_id
 * @property string $path
 * @property string $disk
 * @property int $sort_order
 * @property array $focus
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class CatalogItemImageModel extends Model
{
    protected $table = 'CAT_item_images';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'item_id',
        'path',
        'disk',
        'sort_order',
        'focus',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'item_id' => 'integer',
        'sort_order' => 'integer',
        'focus' => 'array',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(CatalogItemModel::class, 'item_id');
    }
}
