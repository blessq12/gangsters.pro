<?php

namespace Tests\Unit\Shopping\CheckoutDraft;

use App\Application\Shopping\CheckoutDraft\SyncCheckoutDraftGiftPromotion;
use App\Domain\Shopping\CartRules\CartState;
use App\Domain\Shopping\CartRules\Rules\GiftPromotionRule;
use App\Domain\Shopping\Entities\ShoppingSession;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class SyncCheckoutDraftGiftPromotionTest extends TestCase
{
    private function sessionWithDraft(?array $draft): ShoppingSession
    {
        $now = new DateTimeImmutable;

        return new ShoppingSession(
            1,
            'test-public-id',
            null,
            $now->modify('+1 day'),
            [],
            [],
            $draft,
            $now,
            $now,
        );
    }

    public function test_clears_gift_from_draft_when_not_applied(): void
    {
        $session = $this->sessionWithDraft([
            'promotions' => [
                'free_roll_gift_product_id' => 42,
            ],
        ]);

        $resolved = new CartState([], [], [], 0, 0, 0);

        $sync = new SyncCheckoutDraftGiftPromotion;
        $changed = $sync->execute($session, $resolved);

        $this->assertTrue($changed);
        $this->assertNull($session->getCheckoutDraft());
    }

    public function test_does_not_clear_when_gift_applied(): void
    {
        $session = $this->sessionWithDraft([
            'promotions' => [
                'free_roll_gift_product_id' => 42,
            ],
        ]);

        $resolved = new CartState(
            [],
            [],
            [
                GiftPromotionRule::PROMO_KEY => [
                    'phase' => 'gift_applied',
                    'eligible' => true,
                    'selected_product_id' => 42,
                ],
            ],
            0,
            0,
            0,
        );

        $sync = new SyncCheckoutDraftGiftPromotion;
        $changed = $sync->execute($session, $resolved);

        $this->assertFalse($changed);
        $this->assertSame(42, $session->getCheckoutDraft()['promotions']['free_roll_gift_product_id']);
    }

    public function test_no_op_when_draft_has_no_gift(): void
    {
        $session = $this->sessionWithDraft(['delivery_info' => ['method' => 'courier']]);

        $resolved = new CartState([], [], [], 0, 0, 0);

        $sync = new SyncCheckoutDraftGiftPromotion;
        $this->assertFalse($sync->execute($session, $resolved));
    }
}
