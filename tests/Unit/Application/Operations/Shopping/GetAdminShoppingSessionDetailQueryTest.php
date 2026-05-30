<?php

namespace Tests\Unit\Application\Operations\Shopping;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Operations\Client\Contracts\AdminClientReadRepository;
use App\Application\Operations\Shopping\Contracts\AdminShoppingProductReadRepository;
use App\Application\Operations\Shopping\Presenter\AdminShoppingSessionPresenter;
use App\Application\Operations\Shopping\Query\GetAdminShoppingSessionDetailQuery;
use App\Domain\Shopping\Entities\CartLine;
use App\Domain\Shopping\Entities\ShoppingSession;
use App\Domain\Shopping\Repositories\ShoppingSessionRepositoryInterface;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PHPUnit\Framework\TestCase;

final class GetAdminShoppingSessionDetailQueryTest extends TestCase
{
    public function test_returns_session_snapshot_by_id(): void
    {
        $session = $this->sampleSession();
        $sessions = $this->createMock(ShoppingSessionRepositoryInterface::class);
        $sessions->method('getById')->with(10)->willReturn($session);

        $query = new GetAdminShoppingSessionDetailQuery(
            $sessions,
            $this->presenter(),
        );

        $result = $query->execute(10);

        $this->assertSame(10, $result['session']['id']);
        $this->assertSame('pub-10', $result['session']['public_id']);
        $this->assertSame('Клиент #5', $result['client']['label']);
    }

    public function test_maps_not_found_to_api_exception(): void
    {
        $sessions = $this->createMock(ShoppingSessionRepositoryInterface::class);
        $sessions->method('getById')->willThrowException(new ModelNotFoundException);

        $query = new GetAdminShoppingSessionDetailQuery(
            $sessions,
            $this->presenter(),
        );

        $this->expectException(ApiException::class);
        $query->execute(999);
    }

    private function presenter(): AdminShoppingSessionPresenter
    {
        $products = $this->createMock(AdminShoppingProductReadRepository::class);
        $products->method('findSummariesByIds')->willReturn([]);

        $clients = $this->createMock(AdminClientReadRepository::class);
        $clients->method('findProfileSummaryById')->willReturn(null);

        return new AdminShoppingSessionPresenter($products, $clients);
    }

    private function sampleSession(): ShoppingSession
    {
        $now = new DateTimeImmutable('2026-01-01 12:00:00');

        return new ShoppingSession(
            id: 10,
            publicId: 'pub-10',
            clientId: 5,
            expiresAt: $now->modify('+1 day'),
            cartLines: [new CartLine(productId: 42, quantity: 2)],
            favoriteProductIds: [1, 2],
            checkoutDraft: [
                'guest_contact' => ['phone' => '+79991234567'],
                'delivery_info' => [
                    'method' => 'courier',
                    'address' => ['street' => 'Ленина', 'house' => '1'],
                ],
            ],
            createdAt: $now,
            updatedAt: $now,
        );
    }
}
