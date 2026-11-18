<?php

namespace App\Infrastructure\Catalog\Model;

use Illuminate\Database\Eloquent\Model;

final class CAT_Tag extends Model
{
    protected $table = 'CAT_tags';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'label',
        'color',
        'is_active',
        'sort_order',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
