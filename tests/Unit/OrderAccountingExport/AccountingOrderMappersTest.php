<?php

namespace Tests\Unit\OrderAccountingExport;

use App\Application\OrderAccountingExport\Mapper\FrontpadOrderMapper;
use App\Domain\Order\Enum\OrderSource;
use App\Domain\Order\Event\OrderCreated;
use App\Domain\Order\ValueObject\OrderCartSnapshot;
use App\Domain\Order\ValueObject\OrderClientSnapshot;
use App\Domain\Order\ValueObject\OrderDeliveryAddress;
use App\Domain\Order\ValueObject\OrderDeliverySnapshot;
use App\Domain\Order\ValueObject\OrderGuestContact;
use App\Domain\Order\ValueObject\OrderId;
use App\Domain\Order\ValueObject\OrderLineSnapshot;
use App\Domain\Order\ValueObject\OrderPaymentSnapshot;
use App\Domain\OrderAccountingExport\Exception\UnknownAccountingProductException;
use App\Domain\OrderAccountingExport\Repository\AccountingProductBindingRepository;
use App\Shared\Enum\DeliveryMethod;
use App\Shared\Enum\PaymentMethod;
use App\Shared\ValueObject\Money;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class AccountingOrderMappersTest extends TestCase
{
    #[Test]
    public function frontpad_маппер_собирает_form_параметры(): void
    {
        config([
            'order-accounting-export.systems.frontpad.secret' => 'test-secret',
            'order-accounting-export.systems.frontpad.pay.card_online' => '3',
            'order-accounting-export.systems.frontpad.hook_status' => [1, 10, 11],
            'app.url' => 'https://example.test',
        ]);

        $request = (new FrontpadOrderMapper())->toRequest($this->sampleEvent(sku: '001'));

        $this->assertSame('test-secret', $request['secret']);
        $this->assertSame('79990001122', $request['phone']);
        $this->assertSame('Иван', $request['name']);
        $this->assertSame('3', $request['pay']);
        $this->assertSame(1, $request['person']);
        $this->assertSame(1, $request['product'][0]);
        $this->assertSame(2, $request['product_kol'][0]);
        $this->assertArrayNotHasKey('product_price', $request);
        $this->assertSame('Ленина', $request['street']);
        $this->assertSame('10', $request['home']);
        $this->assertSame('https://example.test/api/orders/update', $request['hook_url']);
        $this->assertSame([1, 10, 11], $request['hook_status']);
    }

    #[Test]
    public function frontpad_маппер_требует_sku_каталога(): void
    {
        config([
            'order-accounting-export.systems.frontpad.secret' => 'test-secret',
        ]);

        $this->expectException(UnknownAccountingProductException::class);
        $this->expectExceptionMessage('не имеет SKU каталога');

        (new FrontpadOrderMapper())->toRequest($this->sampleEvent(sku: null));
    }

    #[Test]
    public function frontpad_маппер_включает_подарок_и_комплект(): void
    {
        config([
            'order-accounting-export.systems.frontpad.secret' => 'test-secret',
        ]);

        $request = (new FrontpadOrderMapper())->toRequest(new OrderCreated(
            orderId: OrderId::fromInt(42),
            source: OrderSource::Site,
            checkoutId: 'chk-1',
            aggregatorReference: null,
            cart: OrderCartSnapshot::fromLines([
                new OrderLineSnapshot(
                    productId: 10,
                    productName: 'Филадельфия',
                    quantity: 2,
                    unitPrice: Money::rubles(450),
                    sku: '001',
                ),
                new OrderLineSnapshot(
                    productId: 20,
                    productName: 'Имбирь',
                    quantity: 1,
                    unitPrice: Money::rubles(0),
                    payload: ['kind' => 'complement'],
                    sku: 'cmp-1',
                ),
                new OrderLineSnapshot(
                    productId: 30,
                    productName: 'Суп подарок',
                    quantity: 1,
                    unitPrice: Money::rubles(0),
                    payload: ['kind' => 'gift'],
                    sku: 'gift-1',
                ),
            ]),
            client: OrderClientSnapshot::guest(new OrderGuestContact(
                name: 'Иван',
                phone: '+79990001122',
                email: null,
            )),
            delivery: new OrderDeliverySnapshot(
                method: DeliveryMethod::Pickup,
                address: null,
                comment: null,
                scheduledAt: null,
            ),
            payment: new OrderPaymentSnapshot(
                method: PaymentMethod::Cash,
                changeFromRubles: null,
            ),
            occurredAt: new DateTimeImmutable('2026-06-16T12:00:00+00:00'),
        ));

        $this->assertSame([1, 'cmp-1', 'gift-1'], $request['product']);
        $this->assertSame([2, 1, 1], $request['product_kol']);
        $this->assertSame([1 => 0, 2 => 0], $request['product_price']);
    }

    #[Test]
    public function frontpad_маппер_передаёт_комментарий_доставки_в_descr(): void
    {
        config([
            'order-accounting-export.systems.frontpad.secret' => 'test-secret',
        ]);

        $request = (new FrontpadOrderMapper())->toRequest(new OrderCreated(
            orderId: OrderId::fromInt(42),
            source: OrderSource::Site,
            checkoutId: 'chk-1',
            aggregatorReference: null,
            cart: OrderCartSnapshot::fromLines([
                new OrderLineSnapshot(
                    productId: 10,
                    productName: 'Филадельфия',
                    quantity: 1,
                    unitPrice: Money::rubles(450),
                    sku: '001',
                ),
            ]),
            client: OrderClientSnapshot::guest(new OrderGuestContact(
                name: 'Иван',
                phone: '+79990001122',
                email: null,
            )),
            delivery: new OrderDeliverySnapshot(
                method: DeliveryMethod::Courier,
                address: new OrderDeliveryAddress(
                    street: 'Ленина',
                    house: '10',
                    entrance: null,
                    apartment: null,
                ),
                comment: 'Без имбиря. Сдача с 2000 ₽',
                scheduledAt: null,
            ),
            payment: new OrderPaymentSnapshot(
                method: PaymentMethod::Cash,
                changeFromRubles: 2000,
            ),
            occurredAt: new DateTimeImmutable('2026-06-16T12:00:00+00:00'),
        ));

        $this->assertSame(
            'Заказ #42. Без имбиря. Сдача с 2000 ₽',
            $request['descr'],
        );
    }

    private function sampleEvent(?string $sku = '001'): OrderCreated
    {
        return new OrderCreated(
            orderId: OrderId::fromInt(42),
            source: OrderSource::Site,
            checkoutId: 'chk-1',
            aggregatorReference: null,
            cart: OrderCartSnapshot::fromLines([
                new OrderLineSnapshot(
                    productId: 10,
                    productName: 'Филадельфия',
                    quantity: 2,
                    unitPrice: Money::rubles(450),
                    sku: $sku,
                ),
            ]),
            client: OrderClientSnapshot::guest(new OrderGuestContact(
                name: 'Иван',
                phone: '+7 (999) 000-11-22',
                email: 'ivan@example.com',
            )),
            delivery: new OrderDeliverySnapshot(
                method: DeliveryMethod::Courier,
                address: new OrderDeliveryAddress(
                    street: 'Ленина',
                    house: '10',
                    entrance: '1',
                    apartment: '5',
                ),
                comment: 'Без имбиря',
                scheduledAt: null,
            ),
            payment: new OrderPaymentSnapshot(
                method: PaymentMethod::CardOnline,
                changeFromRubles: null,
            ),
            occurredAt: new DateTimeImmutable('2026-06-16T12:00:00+00:00'),
        );
    }
}
