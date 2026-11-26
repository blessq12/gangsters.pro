<?php

namespace Tests\Domain\Order;

use App\Domain\Core\Order\Entity\OrderCommercialRules;
use App\Domain\Core\Order\Enum\OrderLineType;
use App\Domain\Core\Order\Policy\OrderFormation;
use App\Domain\Core\Order\ValueObject\OrderDeliveryAddress;
use App\Domain\Core\Order\ValueObject\OrderDeliveryInfo;
use App\Domain\Core\Order\ValueObject\OrderFormationFacts;
use App\Domain\Core\Order\ValueObject\OrderLine;
use App\Shared\Geo\PointInGeoJsonZone;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Те же эталоны гоняет JS-движок (apps/public/modules/checkout/domain/formation/formCheckout.test.js).
 */
final class OrderFormationParityTest extends TestCase
{
    private const FIXTURE = __DIR__.'/../../fixtures/order-formation/cases.json';

    /**
     * @return iterable<string, array{0: array<string, mixed>, 1: array<string, mixed>}>
     */
    public static function cases(): iterable
    {
        $fixture = json_decode((string) file_get_contents(self::FIXTURE), true, flags: JSON_THROW_ON_ERROR);
        foreach ($fixture['cases'] as $case) {
            yield $case['name'] => [$fixture['context'], $case];
        }
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array<string, mixed>  $case
     */
    #[DataProvider('cases')]
    public function test_эталон(array $context, array $case): void
    {
        $intent = $case['intent'];
        $timezone = new DateTimeZone($context['timezone']);

        [$baseLines, $rollsPerUnit] = $this->baseLines($context, $intent['lines']);

        $isCourier = $intent['delivery_method'] === 'courier';
        $zone = null;
        if ($isCourier) {
            $lat = (float) $intent['address']['lat'];
            $lng = (float) $intent['address']['lng'];
            foreach ($context['delivery']['zones'] as $candidate) {
                if (PointInGeoJsonZone::contains($candidate['geometry'], $lat, $lng) === true) {
                    $zone = $candidate;
                    break;
                }
            }
            $delivery = OrderDeliveryInfo::courier(
                address: OrderDeliveryAddress::create(
                    street: $intent['address']['street'],
                    house: $intent['address']['house'],
                    lat: $lat,
                    lng: $lng,
                ),
                zoneId: $zone['id'] ?? 'outside',
                feeKopecks: $zone['fee_kopecks'] ?? 0,
            );
        } else {
            $delivery = OrderDeliveryInfo::pickup();
        }

        $giftProductId = (int) ($intent['gift_product_id'] ?? 0);
        $gift = null;
        foreach ($context['commercial_rules']['gift_candidates'] as $candidate) {
            if ($candidate['product_id'] === $giftProductId) {
                $gift = $candidate;
            }
        }

        $selections = [];
        foreach ($intent['complement_selections'] ?? [] as $row) {
            $selections[(int) $row['product_id']] = (int) $row['quantity'];
        }

        $at = isset($intent['scheduled_at'])
            ? new DateTimeImmutable($intent['scheduled_at'], $timezone)
            : new DateTimeImmutable($case['now']);

        $formed = OrderFormation::apply(
            baseLines: $baseLines,
            delivery: $delivery,
            rules: new OrderCommercialRules(
                id: 1,
                branchId: $context['branch_id'],
                giftRules: $context['commercial_rules']['gift_rules'],
                deliveryBenefit: $context['commercial_rules']['delivery_benefit'],
                complementSetBenefit: $context['commercial_rules']['complement_set_benefit'],
            ),
            facts: new OrderFormationFacts(
                isRollByProductId: [],
                complements: $context['commercial_rules']['complements'],
                giftCandidates: $context['commercial_rules']['gift_candidates'],
                gift: $gift,
                rollsPerUnitByLineId: $rollsPerUnit,
                complementSelections: $selections,
            ),
            channel: $intent['delivery_method'],
            at: $at,
            timezone: $context['timezone'],
            zoneIsRemote: $isCourier && ($zone === null || $zone['is_remote']),
        );

        $itemsTotal = 0;
        foreach ($formed['lines'] as $line) {
            if ($line->isBillable()) {
                $itemsTotal += $line->lineTotalKopecks();
            }
        }
        $deliveryFee = $formed['delivery']->feeKopecks();

        $this->assertSame($case['expected']['lines'], array_map(
            static fn (OrderLine $line): string => $line->lineType()->value.':'.$line->productId().'x'.$line->quantity(),
            $formed['lines'],
        ));
        $this->assertSame($case['expected']['items_total'], $itemsTotal);
        $this->assertSame($case['expected']['delivery_fee'], $deliveryFee);
        $this->assertSame($case['expected']['grand_total'], $itemsTotal + $deliveryFee);
        $this->assertSame($case['expected']['in_zone'], $isCourier ? $zone !== null : null);
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  list<array{product_id: int, kind: string, quantity: int}>  $intentLines
     * @return array{0: list<OrderLine>, 1: array<int, int>}
     */
    private function baseLines(array $context, array $intentLines): array
    {
        $products = array_column($context['menu']['products'], null, 'id');
        $sets = array_column($context['menu']['sets'], null, 'id');

        $lines = [];
        $rollsPerUnit = [];
        foreach ($intentLines as $raw) {
            $isSet = $raw['kind'] === 'set';
            $source = $isSet ? $sets[$raw['product_id']] : $products[$raw['product_id']];
            $lines[] = new OrderLine(
                productId: $source['id'],
                name: $source['name'],
                unitPriceKopecks: $source['price_kopecks'],
                quantity: $raw['quantity'],
                lineType: $isSet ? OrderLineType::Set : OrderLineType::Regular,
            );
            $rollsPerUnit[$source['id']] = $source['rolls_per_unit'];
        }

        return [$lines, $rollsPerUnit];
    }
}
