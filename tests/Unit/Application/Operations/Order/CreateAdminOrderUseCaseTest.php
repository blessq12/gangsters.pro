<?php

namespace Tests\Unit\Application\Operations\Order;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Operations\Order\Command\CreateAdminOrderUseCase;
use App\Application\Operations\Order\DTO\CreateAdminOrderDTO;
use Tests\TestCase;

final class CreateAdminOrderUseCaseTest extends TestCase
{
    public function test_rejects_empty_items(): void
    {
        $useCase = app(CreateAdminOrderUseCase::class);

        $this->expectException(ApiException::class);

        $useCase->execute(new CreateAdminOrderDTO(
            clientId: null,
            guestCustomerName: 'Guest',
            guestCustomerPhone: '+79990000001',
            guestCustomerEmail: null,
            items: [],
            deliveryMethod: 'courier',
            deliveryAddress: null,
            deliveryComment: null,
            paymentMethod: 'cash',
        ));
    }

    public function test_guest_requires_name_and_phone(): void
    {
        $useCase = app(CreateAdminOrderUseCase::class);

        $this->expectException(ApiException::class);

        $useCase->execute(new CreateAdminOrderDTO(
            clientId: null,
            guestCustomerName: '',
            guestCustomerPhone: '',
            guestCustomerEmail: null,
            items: [['product_id' => 1, 'quantity' => 1]],
            deliveryMethod: 'courier',
            deliveryAddress: null,
            deliveryComment: null,
            paymentMethod: 'cash',
        ));
    }
}
