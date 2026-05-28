<?php

namespace Tests\Unit\Domain\Shopping\Delivery;

use App\Domain\Order\Enums\DeliveryMethod;
use App\Domain\Shopping\Delivery\DeliveryPricingPolicy;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class DeliveryPricingPolicyTest extends TestCase
{
    private DeliveryPricingPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new DeliveryPricingPolicy;
    }

    public function test_pickup_always_free(): void
    {
        $result = $this->policy->calculate(
            DeliveryMethod::Pickup,
            100_00,
            100_00,
            500_00,
            200_00,
        );

        $this->assertSame(0, $result->deliveryFeeKopecks);
        $this->assertTrue($result->isFree());
        $this->assertSame(0, $result->remainingToFreeKopecks);
    }

    public function test_courier_below_threshold_charges_fee(): void
    {
        $result = $this->policy->calculate(
            DeliveryMethod::Courier,
            300_00,
            300_00,
            500_00,
            150_00,
        );

        $this->assertSame(150_00, $result->deliveryFeeKopecks);
        $this->assertFalse($result->isFree());
        $this->assertSame(200_00, $result->remainingToFreeKopecks);
        $this->assertSame(450_00, $result->grandTotalKopecks);
    }

    public function test_courier_at_threshold_is_free(): void
    {
        $result = $this->policy->calculate(
            DeliveryMethod::Courier,
            500_00,
            500_00,
            500_00,
            150_00,
        );

        $this->assertSame(0, $result->deliveryFeeKopecks);
        $this->assertSame(0, $result->remainingToFreeKopecks);
    }

    public function test_courier_above_threshold_is_free(): void
    {
        $result = $this->policy->calculate(
            DeliveryMethod::Courier,
            600_00,
            600_00,
            500_00,
            150_00,
        );

        $this->assertSame(0, $result->deliveryFeeKopecks);
        $this->assertSame(0, $result->remainingToFreeKopecks);
    }

    public function test_null_threshold_means_free_courier(): void
    {
        $result = $this->policy->calculate(
            DeliveryMethod::Courier,
            100_00,
            100_00,
            null,
            150_00,
        );

        $this->assertSame(0, $result->deliveryFeeKopecks);
        $this->assertNull($result->freeDeliveryThresholdKopecks);
    }

    public function test_null_fee_means_free_courier(): void
    {
        $result = $this->policy->calculate(
            DeliveryMethod::Courier,
            100_00,
            100_00,
            500_00,
            null,
        );

        $this->assertSame(0, $result->deliveryFeeKopecks);
    }

    public function test_both_null_config_means_free(): void
    {
        $result = $this->policy->calculate(
            DeliveryMethod::Courier,
            100_00,
            100_00,
            null,
            null,
        );

        $this->assertSame(0, $result->deliveryFeeKopecks);
        $this->assertNull($result->configuredDeliveryFeeKopecks);
    }

    public function test_threshold_zero_treated_as_no_paid_delivery(): void
    {
        $result = $this->policy->calculate(
            DeliveryMethod::Courier,
            100_00,
            100_00,
            0,
            150_00,
        );

        $this->assertSame(0, $result->deliveryFeeKopecks);
        $this->assertNull($result->freeDeliveryThresholdKopecks);
    }

    public function test_null_method_defaults_to_courier_preview(): void
    {
        $result = $this->policy->calculate(
            null,
            300_00,
            300_00,
            500_00,
            150_00,
        );

        $this->assertSame(DeliveryMethod::Courier, $result->effectiveMethod);
        $this->assertSame(150_00, $result->deliveryFeeKopecks);
    }

    #[DataProvider('negativeFeeClampedProvider')]
    public function test_negative_configured_fee_clamped_to_zero(int $configuredFee): void
    {
        $result = $this->policy->calculate(
            DeliveryMethod::Courier,
            100_00,
            100_00,
            500_00,
            $configuredFee,
        );

        $this->assertSame(0, $result->deliveryFeeKopecks);
    }

    /**
     * @return array<string, array{int}>
     */
    public static function negativeFeeClampedProvider(): array
    {
        return [
            'negative' => [-100],
            'zero' => [0],
        ];
    }
}
