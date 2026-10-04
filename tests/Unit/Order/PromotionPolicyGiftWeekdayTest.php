<?php

namespace Tests\Unit\Order;

use App\Domain\Order\Entity\PromotionPolicy;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

final class PromotionPolicyGiftWeekdayTest extends TestCase
{
    public function test_empty_weekdays_means_any_day(): void
    {
        $policy = $this->policy();
        $rule = [
            'is_active' => true,
            'allowed_weekdays' => [],
        ];

        $this->assertTrue(
            $policy->isGiftRuleAllowedOnDate(
                $rule,
                Carbon::parse('2026-10-09 12:00:00', 'Asia/Tomsk'),
                'Asia/Tomsk',
            ),
        );
    }

    public function test_courier_weekdays_mon_thu_only(): void
    {
        $policy = $this->policy();
        $rule = [
            'is_active' => true,
            'allowed_weekdays' => [1, 2, 3, 4],
        ];

        $this->assertTrue(
            $policy->isGiftRuleAllowedOnDate(
                $rule,
                Carbon::parse('2026-10-05 12:00:00', 'Asia/Tomsk'),
                'Asia/Tomsk',
            ),
        );
        $this->assertFalse(
            $policy->isGiftRuleAllowedOnDate(
                $rule,
                Carbon::parse('2026-10-09 12:00:00', 'Asia/Tomsk'),
                'Asia/Tomsk',
            ),
        );
    }

    private function policy(): PromotionPolicy
    {
        return new PromotionPolicy(
            id: 1,
            giftRules: [],
            deliveryBenefit: [
                'is_active' => false,
                'free_delivery_threshold_kopecks' => 0,
                'outside_zone_surcharge_kopecks' => 0,
                'below_threshold_fee_mode' => 'base',
                'in_zone_at_threshold_fee_mode' => 'free',
                'outside_zone_at_threshold_fee_mode' => 'outside_zone_surcharge_only',
            ],
            complementSetBenefit: [
                'rolls_per_set' => 2,
                'is_active' => false,
            ],
        );
    }
}
