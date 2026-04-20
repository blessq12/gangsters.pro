<?php

namespace App\Infrastructure\Shopping\Model;

use App\Domain\Shopping\CartRules\ShoppingCartRuleEngine;
use App\Infrastructure\Shopping\CartRules\ShoppingCartRuleConfigRegistrar;
use Illuminate\Database\Eloquent\Model;

class SHP_ShoppingCartRuleSetting extends Model
{
    protected $table = 'SHP_shopping_cart_rule_settings';

    protected $fillable = [
        'complement_rule_enabled',
        'gift_rule_enabled',
        'gift_threshold_kopecks',
        'rolls_per_complement',
        'complement_rule_sort',
        'gift_rule_sort',
    ];

    protected function casts(): array
    {
        return [
            'complement_rule_enabled' => 'boolean',
            'gift_rule_enabled' => 'boolean',
            'gift_threshold_kopecks' => 'integer',
            'rolls_per_complement' => 'integer',
            'complement_rule_sort' => 'integer',
            'gift_rule_sort' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(static function (): void {
            ShoppingCartRuleConfigRegistrar::apply();
            if (app()->resolved(ShoppingCartRuleEngine::class)) {
                app()->forgetInstance(ShoppingCartRuleEngine::class);
            }
        });
    }
}
