<?php

namespace App\Infrastructure\Catalog\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class CAT_TechCard extends Model
{
    protected $table = 'CAT_tech_cards';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'product_id' => 'integer',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(CAT_TechCardLine::class, 'tech_card_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
