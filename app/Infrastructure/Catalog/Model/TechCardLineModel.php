<?php

namespace App\Infrastructure\Catalog\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class TechCardLineModel extends Model
{
    protected $table = 'CAT_tech_card_lines';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tech_card_id',
        'ingredient_ref',
        'quantity',
        'unit',
        'sort_order',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'tech_card_id' => 'integer',
        'quantity' => 'float',
        'sort_order' => 'integer',
    ];

    public function techCard(): BelongsTo
    {
        return $this->belongsTo(TechCardModel::class, 'tech_card_id');
    }
}
