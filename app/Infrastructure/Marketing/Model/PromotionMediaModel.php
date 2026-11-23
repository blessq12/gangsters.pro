<?php

namespace App\Infrastructure\Marketing\Model;

use Illuminate\Database\Eloquent\Model;

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
