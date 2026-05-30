<?php

namespace Tests\Unit\Application\Operations\Shopping;

use App\Application\Operations\Client\Contracts\AdminClientReadRepository;
use App\Application\Operations\Shopping\Contracts\AdminShoppingProductReadRepository;
use App\Application\Operations\Shopping\Presenter\AdminShoppingSessionPresenter;
use App\Domain\Shopping\Entities\CartLine;
use App\Domain\Shopping\Entities\ShoppingSession;
use App\Support\Shopping\AdminCheckoutDraftFormatter;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class AdminShoppingSessionPresenterTest extends TestCase
{
    public function test_present_formats_guest_session_with_cart_and_checkout_sections(): void
    {
        $products = $this->createMock(AdminShoppingProductReadRepository::class);
        $products->method('findSummariesByIds')->willReturn([]);

        $presenter = new AdminShoppingSessionPresenter(
            $products,
            $this->createMock(AdminClientReadRepository::class),
        );

        $now = new DateTimeImmutable('2026-01-01 12:00:00');
        $session = new ShoppingSession(
            id: 1,
            publicId: 'pub-1',
            clientId: null,
            expiresAt: $now->modify('+1 day'),
            cartLines: [new CartLine(productId: 7, quantity: 3)],
            favoriteProductIds: [],
            checkoutDraft: [
                'guest_contact' => ['name' => 'Иван', 'phone' => '+79991234567'],
                'payment_info' => ['method' => 'cash'],
            ],
            createdAt: $now,
            updatedAt: $now,
        );

        $result = $presenter->present($session);

        $this->assertSame('Гость', $result['client']['label']);
        $this->assertSame('gray', $result['client']['badge_color']);
        $this->assertSame('01.01.2026 12:00', $result['session']['created_at']);
        $this->assertSame(3, $result['cart']['total_quantity']);
        $this->assertSame('Товар #7', $result['cart']['lines'][0]['product_name']);
        $this->assertTrue($result['checkout']['has_draft']);
        $this->assertCount(2, $result['checkout']['sections']);
        $this->assertArrayNotHasKey('raw_json', $result['checkout']);
    }

    public function test_present_uses_product_summaries_and_client_profile_summary(): void
    {
        $products = $this->createMock(AdminShoppingProductReadRepository::class);
        $products->method('findSummariesByIds')->willReturn([
            7 => [
                'id' => 7,
                'name' => 'Филадельфия',
                'price_kopecks' => 45000,
                'status' => 'active',
            ],
        ]);

        $clients = $this->createMock(AdminClientReadRepository::class);
        $clients->method('findProfileSummaryById')->with(5)->willReturn([
            'id' => 5,
            'name' => 'Анна',
            'phone' => '+79990001122',
            'email' => 'anna@example.com',
        ]);

        $presenter = new AdminShoppingSessionPresenter($products, $clients);

        $now = new DateTimeImmutable('2026-01-01 12:00:00');
        $session = new ShoppingSession(
            id: 1,
            publicId: 'pub-1',
            clientId: 5,
            expiresAt: $now->modify('+1 day'),
            cartLines: [new CartLine(productId: 7, quantity: 2)],
            favoriteProductIds: [],
            checkoutDraft: null,
            createdAt: $now,
            updatedAt: $now,
        );

        $result = $presenter->present($session);

        $this->assertSame('Анна', $result['client']['label']);
        $this->assertSame('+79990001122', $result['client']['phone']);
        $this->assertSame('Филадельфия', $result['cart']['lines'][0]['product_name']);
        $this->assertSame('450,00 ₽', $result['cart']['lines'][0]['unit_price_label']);
        $this->assertSame('900,00 ₽', $result['cart']['lines'][0]['line_total_label']);
    }

    public function test_checkout_draft_formatter_builds_readable_sections(): void
    {
        $sections = AdminCheckoutDraftFormatter::sections([
            'delivery_info' => [
                'method' => 'pickup',
            ],
            'payment_info' => [
                'method' => 'card',
                'change_from' => 1000,
            ],
        ]);

        $this->assertCount(2, $sections);
        $this->assertSame('Доставка', $sections[0]['title']);
        $this->assertSame('Самовывоз', $sections[0]['rows'][0]['value']);
        $this->assertSame('Банковская карта', $sections[1]['rows'][0]['value']);
    }
}
