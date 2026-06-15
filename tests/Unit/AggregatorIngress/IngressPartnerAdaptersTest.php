<?php

namespace Tests\Unit\AggregatorIngress;

use App\Infrastructure\AggregatorIngress\Adapter\ChibbisIngressPartnerAdapter;
use App\Infrastructure\AggregatorIngress\Adapter\KuperIngressPartnerAdapter;
use App\Infrastructure\AggregatorIngress\Adapter\YandexEdaIngressPartnerAdapter;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class IngressPartnerAdaptersTest extends TestCase
{
    #[Test]
    public function yandex_eda_маппит_заказ(): void
    {
        $mapped = (new YandexEdaIngressPartnerAdapter())->map([
            'order_id' => 'ye-100',
            'created_at' => '2026-06-15T12:00:00+00:00',
            'customer' => [
                'name' => 'Иван',
                'phone' => '+79990001122',
            ],
            'delivery' => [
                'type' => 'courier',
                'address' => [
                    'street' => 'Ленина',
                    'house' => '10',
                    'apartment' => '5',
                ],
            ],
            'payment' => [
                'type' => 'card_online',
            ],
            'items' => [
                [
                    'id' => 'YE-SKU-1',
                    'quantity' => 2,
                    'price_rubles' => 450,
                ],
            ],
        ]);

        $this->assertSame('ye-100', $mapped->externalOrderId);
        $this->assertSame('Иван', $mapped->clientName);
        $this->assertCount(1, $mapped->lines);
        $this->assertSame('YE-SKU-1', $mapped->lines[0]->partnerSku);
        $this->assertSame(450, $mapped->lines[0]->unitPriceRubles);
    }

    #[Test]
    public function chibbis_маппит_заказ(): void
    {
        $mapped = (new ChibbisIngressPartnerAdapter())->map([
            'orderId' => 'ch-200',
            'createdAt' => '2026-06-15T13:00:00+00:00',
            'client' => [
                'fullName' => 'Пётр',
                'phoneNumber' => '+79990002233',
            ],
            'deliveryType' => 'pickup',
            'paymentType' => 'cash',
            'products' => [
                [
                    'vendorCode' => 'CH-SKU-9',
                    'amount' => 1,
                    'price' => 300,
                ],
            ],
        ]);

        $this->assertSame('ch-200', $mapped->externalOrderId);
        $this->assertSame('Пётр', $mapped->clientName);
        $this->assertSame('pickup', $mapped->deliveryMethod->value);
        $this->assertSame('CH-SKU-9', $mapped->lines[0]->partnerSku);
    }

    #[Test]
    public function kuper_маппит_заказ(): void
    {
        $mapped = (new KuperIngressPartnerAdapter())->map([
            'order' => [
                'uuid' => 'kp-300',
                'created_at' => '2026-06-15T14:00:00+00:00',
            ],
            'user' => [
                'name' => 'Анна',
                'phone' => '+79990003344',
            ],
            'shipment' => [
                'type' => 'courier',
                'address' => [
                    'street' => 'Мира',
                    'house' => '3',
                ],
            ],
            'payment' => [
                'method' => 'prepaid',
            ],
            'positions' => [
                [
                    'id' => 'KP-SKU-7',
                    'quantity' => 3,
                    'price_kopecks' => 25000,
                ],
            ],
        ]);

        $this->assertSame('kp-300', $mapped->externalOrderId);
        $this->assertSame('Анна', $mapped->clientName);
        $this->assertSame('KP-SKU-7', $mapped->lines[0]->partnerSku);
        $this->assertSame(250, $mapped->lines[0]->unitPriceRubles);
    }
}
