<?php

namespace App\Infrastructure\Marketing\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property string $body
 * @property string $image
 * @property int $sort_order
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class PromotionMediaModel extends Model
{
    protected $table = 'MKT_promotions';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'body',
        'image',
        'sort_order',
        'is_active',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];
}
