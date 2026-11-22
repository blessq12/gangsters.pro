<?php

namespace Tests\Domain\Order;

use App\Domain\Core\Order\Enum\OrderLineType;
use App\Domain\Core\Order\Policy\OrderFormation;
use App\Domain\Core\Order\Policy\PromotionPolicy;
use App\Domain\Core\Order\ValueObject\OrderDeliveryAddress;
use App\Domain\Core\Order\ValueObject\OrderDeliveryInfo;
use App\Domain\Core\Order\ValueObject\OrderFormationFacts;
use App\Domain\Core\Order\ValueObject\OrderLine;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class OrderFormationTest extends TestCase
{
    public function testПодарокИВсеКомплектыДобавляютсяПоПолитике(): void
    {
        $formed = OrderFormation::apply(
            baseLines: [
                $this->line(1, 'Ролл', 50000, 8, OrderLineType::Regular),
                $this->line(2, 'Сет', 30000, 1, OrderLineType::Set),
            ],
            delivery: OrderDeliveryInfo::pickup(),
            policy: $this->policy(giftMin: 10000, rollsPerSet: 4, maxSets: 3, deliveryActive: false, giftProductId: 20),
            facts: new OrderFormationFacts(
                isRollByProductId: [1 => true, 2 => false],
                complements: [
                    ['product_id' => 10, 'name' => 'Соус'],
                    ['product_id' => 11, 'name' => 'Имбирь'],
                ],
                gift: ['product_id' => 20, 'name' => 'Подарок'],
            ),
            channel: 'pickup',
            at: new DateTimeImmutable('2026-11-19 12:00:00'),
            timezone: 'UTC',
        );

        $types = array_map(
            static fn (OrderLine $line): string => $line->lineType()->value.':'.$line->productId().'x'.$line->quantity(),
            $formed['lines'],
        );

        $this->assertSame([
            'regular:1x8',
            'set:2x1',
            'complement:10x2',
            'complement:11x2',
            'gift:20x1',
        ], $types);
        $this->assertSame(0, $formed['delivery']->feeKopecks());
    }

    public function testПлатныйКомплектВКорзинеНеМешаетБесплатнойДобавке(): void
    {
        $formed = OrderFormation::apply(
            baseLines: [
                $this->line(1, 'Ролл', 50000, 4, OrderLineType::Regular),
                $this->line(10, 'Соус', 5000, 1, OrderLineType::Regular),
            ],
            delivery: OrderDeliveryInfo::pickup(),
            policy: $this->policy(giftMin: 99999999, rollsPerSet: 4, maxSets: 1, deliveryActive: false),
            facts: new OrderFormationFacts(
                isRollByProductId: [1 => true, 10 => false],
                complements: [
                    ['product_id' => 10, 'name' => 'Соус'],
                ],
                gift: null,
            ),
            channel: 'pickup',
            at: new DateTimeImmutable('2026-11-19 12:00:00'),
            timezone: 'UTC',
        );

        $types = array_map(
            static fn (OrderLine $line): string => $line->lineType()->value.':'.$line->productId().'x'.$line->quantity(),
            $formed['lines'],
        );

        $this->assertSame([
            'regular:1x4',
            'regular:10x1',
            'complement:10x1',
        ], $types);
    }

    public function testВЗонеНижеПорогаОстаётсяБазовыйТариф(): void
    {
        $formed = $this->formCourier(subtotalKopecks: 10000, threshold: 50000, remote: false, baseFee: 20000, surcharge: 5000);

        $this->assertSame(20000, $formed['delivery']->feeKopecks());
    }

    public function testВЗонеОтПорогаДоставкаБесплатная(): void
    {
        $formed = $this->formCourier(subtotalKopecks: 50000, threshold: 50000, remote: false, baseFee: 20000, surcharge: 5000);

        $this->assertSame(0, $formed['delivery']->feeKopecks());
    }

    public function testВнеЗоныОтПорогаОстаётсяТолькоНадбавка(): void
    {
        $formed = $this->formCourier(subtotalKopecks: 80000, threshold: 50000, remote: true, baseFee: 20000, surcharge: 5000);

        $this->assertSame(5000, $formed['delivery']->feeKopecks());
    }

    /**
     * @return array{lines: list<OrderLine>, delivery: OrderDeliveryInfo}
     */
    private function formCourier(
        int $subtotalKopecks,
        int $threshold,
        bool $remote,
        int $baseFee,
        int $surcharge,
    ): array {
        return OrderFormation::apply(
            baseLines: [$this->line(1, 'Ролл', $subtotalKopecks, 1, OrderLineType::Regular)],
            delivery: OrderDeliveryInfo::courier(
                new OrderDeliveryAddress('Ленина', '1'),
                'zone-1',
                $baseFee,
            ),
            policy: $this->policy(
                giftMin: 99999999,
                rollsPerSet: 99,
                maxSets: 1,
                deliveryActive: true,
                threshold: $threshold,
                surcharge: $surcharge,
            ),
            facts: OrderFormationFacts::empty(),
            channel: 'courier',
            at: new DateTimeImmutable('2026-11-19 12:00:00'),
            timezone: 'UTC',
            zoneIsRemote: $remote,
        );
    }

    private function line(
        int $id,
        string $name,
        int $price,
        int $qty,
        OrderLineType $type,
    ): OrderLine {
        return new OrderLine($id, $name, $price, $qty, $type);
    }

    private function policy(
        int $giftMin,
        int $rollsPerSet,
        int $maxSets,
        bool $deliveryActive,
        int $threshold = 0,
        int $surcharge = 0,
        ?int $giftProductId = null,
    ): PromotionPolicy {
        return new PromotionPolicy(
            id: 1,
            branchId: 1,
            giftRules: [[
                'order_channel' => 'pickup',
                'min_order_amount_kopecks' => $giftMin,
                'benefit_type' => 'free_roll_gift',
                'is_active' => true,
                'allowed_weekdays' => [],
            ]],
            deliveryBenefit: [
                'free_delivery_threshold_kopecks' => $threshold,
                'outside_zone_surcharge_kopecks' => $surcharge,
                'below_threshold_fee_mode' => 'base_tariff',
                'in_zone_at_threshold_fee_mode' => 'free',
                'outside_zone_at_threshold_fee_mode' => 'outside_zone_surcharge_only',
                'is_active' => $deliveryActive,
            ],
            complementSetBenefit: [
                'rolls_per_set' => $rollsPerSet,
                'max_sets' => $maxSets,
                'is_active' => true,
            ],
            giftProductId: $giftProductId,
        );
    }
}
