<?php

namespace App\Infrastructure\Marketing\Model;

use Illuminate\Database\Eloquent\Model;

final class MKT_Banner extends Model
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
