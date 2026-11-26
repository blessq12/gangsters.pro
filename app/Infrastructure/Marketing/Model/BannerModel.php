<?php

namespace App\Infrastructure\Marketing\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $image_desktop
 * @property string $image_mobile
 * @property int $sort_order
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class BannerModel extends Model
{
    protected $table = 'MKT_banners';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'image_desktop',
        'image_mobile',
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
