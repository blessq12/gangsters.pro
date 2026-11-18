<?php

namespace App\Infrastructure\Catalog\Model;

use Illuminate\Database\Eloquent\Model;

final class CAT_SetLine extends Model
{
    public $timestamps = false;

    protected $table = 'CAT_set_lines';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'set_item_id',
        'component_item_id',
        'quantity',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'set_item_id' => 'integer',
        'component_item_id' => 'integer',
        'quantity' => 'integer',
    ];
}
