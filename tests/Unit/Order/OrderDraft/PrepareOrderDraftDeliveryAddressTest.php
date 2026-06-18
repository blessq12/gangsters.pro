<?php

namespace Tests\Unit\Order\OrderDraft;

use App\Application\Order\OrderDraft\Services\PrepareOrderDraftDeliveryAddress;
use App\Domain\Delivery\Port\DeliveryAddressGeocoderPort;
use App\Domain\Delivery\Repository\DeliveryConfigurationRepository;
use App\Domain\Order\OrderDraft\ValueObject\DeliveryAddress;
use App\Shared\Enum\DeliveryMethod;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PrepareOrderDraftDeliveryAddressTest extends TestCase
{
    #[Test]
    public function перегеокодирует_адрес_даже_если_переданы_устаревшие_координаты(): void
    {
        $geocoder = $this->createMock(DeliveryAddressGeocoderPort::class);
        $geocoder->expects($this->once())
            ->method('geocode')
            ->with('Новая', '5', null)
            ->willReturn([
                'latitude' => 55.1,
                'longitude' => 82.9,
            ]);

        $configurationRepository = $this->createMock(DeliveryConfigurationRepository::class);
        $configurationRepository->method('findPublic')->willReturn(null);

        $service = new PrepareOrderDraftDeliveryAddress(
            $configurationRepository,
            $geocoder,
        );

        $result = $service->prepare(
            DeliveryMethod::Courier,
            new DeliveryAddress(
                street: 'Новая',
                house: '5',
                latitude: 54.0,
                longitude: 83.0,
            ),
        );

        $this->assertInstanceOf(DeliveryAddress::class, $result);
        $this->assertSame(55.1, $result->latitude());
        $this->assertSame(82.9, $result->longitude());
    }
}
