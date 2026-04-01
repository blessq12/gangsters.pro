<?php

namespace App\Infrastructure\Order\Model;

use App\Infrastructure\Category\Model\PRD_Category;
use App\Infrastructure\Product\Model\PRD_Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ComplimentaryItemRule extends Model
{
    use HasFactory;

    protected $table = 'complimentary_item_rules';

    protected $fillable = [
        'trigger_category_id',
        'gift_product_id',
        'is_active',
        'priority',
    ];

    protected $casts = [
        'trigger_category_id' => 'int',
        'gift_product_id' => 'int',
        'is_active' => 'bool',
        'priority' => 'int',
    ];

    public function triggerCategory(): BelongsTo
    {
        return $this->belongsTo(PRD_Category::class, 'trigger_category_id');
    }

    public function giftProduct(): BelongsTo
    {
        return $this->belongsTo(PRD_Product::class, 'gift_product_id');
    }

    public function triggerCategories(): BelongsToMany
    {
        return $this->belongsToMany(
            PRD_Category::class,
            'complimentary_item_rule_categories',
            'rule_id',
            'category_id',
        )->withTimestamps();
    }
}
