<?php

namespace Tests\Unit\YandexFood;

use App\Application\YandexFood\Presenter\YandexFoodMenuPresenter;
use App\Domain\Catalog\Entity\Category;
use App\Domain\Catalog\Entity\Product;
use App\Domain\Catalog\Enum\ProductStatus;
use App\Shared\ValueObject\Money;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class YandexFoodMenuPresenterTest extends TestCase
{
    #[Test]
    public function composition_отдаёт_partner_sku_как_id_товара(): void
    {
        $category = new Category(
            id: 5,
            name: 'Роллы',
            slug: 'rolls',
            sortOrder: 10,
            isActive: true,
        );

        $product = new Product(
            id: 42,
            name: 'Филадельфия',
            slug: 'philadelphia',
            sku: 'SKU-42',
            status: ProductStatus::Active,
            isSystem: false,
            price: Money::rubles(450),
            description: 'Лосось, сыр',
            nutrition: null,
            tagIds: [],
            ingredients: [],
            images: [],
        );

        $presenter = new YandexFoodMenuPresenter();
        $payload = $presenter->presentComposition(
            categories: [
                ['category' => $category, 'has_items' => true],
            ],
            products: [
                [
                    'partner_sku' => 'YE-MENU-001',
                    'category_id' => 5,
                    'product' => $product,
                    'sort_order' => 20,
                ],
            ],
            changedAt: Carbon::parse('2026-06-15T12:00:00+00:00'),
        );

        $this->assertSame('YE-MENU-001', $payload['items'][0]['id']);
        $this->assertSame('5', $payload['items'][0]['categoryId']);
        $this->assertSame(450.0, $payload['items'][0]['price']);
        $this->assertSame('Роллы', $payload['categories'][0]['name']);
    }

    #[Test]
    public function availability_отдаёт_quantity_0(): void
    {
        $presenter = new YandexFoodMenuPresenter();

        $payload = $presenter->presentAvailability(['YE-MENU-001', 'YE-MENU-002']);

        $this->assertSame([
            ['id' => 'YE-MENU-001', 'quantity' => 0],
            ['id' => 'YE-MENU-002', 'quantity' => 0],
        ], $payload['items']);
    }
}
