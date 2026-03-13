<?php

namespace Tests\Feature\Catalog;

use App\Application\Catalog\Query\GetCatalogTreeUseCase;
use App\Application\Category\Presenter\CategoryPresenter;
use App\Application\Product\Presenter\ProductPresenter;
use App\Domain\Category\Entity\Category;
use App\Domain\Category\Entity\CategoryProduct;
use App\Domain\Category\Repository\CategoryRepository;
use App\Domain\Product\Entity\Product;
use App\Domain\Product\Entity\ProductImage;
use App\Domain\Product\Entity\ProductIngredient;
use App\Domain\Product\Repository\ProductRepository;
use App\Domain\Product\VO\CustomerStatus;
use App\Domain\Product\VO\ImageVariant;
use App\Domain\Product\VO\Nutrition;
use App\Domain\Product\VO\Price;
use App\Domain\Product\VO\ProductTag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CatalogApiTest extends TestCase
{
    use RefreshDatabase;

    private InMemoryCategoryRepository $categoryRepo;
    private InMemoryProductRepository $productRepo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->categoryRepo = new InMemoryCategoryRepository();
        $this->productRepo = new InMemoryProductRepository();

        $this->app->bind(CategoryRepository::class, fn () => $this->categoryRepo);
        $this->app->bind(ProductRepository::class, fn () => $this->productRepo);

        $this->app->bind(GetCatalogTreeUseCase::class, function ($app) {
            return new GetCatalogTreeUseCase(
                categories: $this->categoryRepo,
                products: $this->productRepo,
                categoryPresenter: new CategoryPresenter(),
                productPresenter: new ProductPresenter(),
            );
        });

        $this->seedCatalog();
    }

    private function seedCatalog(): void
    {
        // Категории
        $rolls = Category::create('Роллы', 'rolls', 1, true);
        $sets = Category::create('Сеты', 'sets', 2, true);

        $rolls = $this->categoryRepo->addCategory($rolls);
        $sets = $this->categoryRepo->addCategory($sets);

        // Продукты
        $nutrition = new Nutrition(250, 8, 10, 30);

        $baseImage = ProductImage::create([
            new ImageVariant('thumb', '/images/p1-thumb.jpg', 200, 200),
            new ImageVariant('medium', '/images/p1-medium.jpg', 600, 400),
            new ImageVariant('large', '/images/p1-large.jpg', 1200, 800),
        ]);

        $california = Product::create(
            name: 'Калифорния',
            description: 'Ролл Калифорния с крабом',
            nutrition: $nutrition,
            images: [$baseImage],
            ingredients: [
                ProductIngredient::create('Рис'),
                ProductIngredient::create('Краб'),
            ],
            tags: [new ProductTag('popular')],
            prices: [new Price(49900, new CustomerStatus('regular'), true)],
        );

        $philadelphia = Product::create(
            name: 'Филадельфия',
            description: 'Ролл Филадельфия с лососем',
            nutrition: $nutrition,
            images: [$baseImage],
            ingredients: [
                ProductIngredient::create('Рис'),
                ProductIngredient::create('Лосось'),
            ],
            tags: [new ProductTag('kids_friendly')],
            prices: [new Price(54900, new CustomerStatus('regular'), true)],
        );

        $set = Product::create(
            name: 'Сет “Популярный”',
            description: 'Набор из топовых роллов',
            nutrition: $nutrition,
            images: [$baseImage],
            ingredients: [],
            tags: [new ProductTag('popular')],
            prices: [new Price(149900, new CustomerStatus('regular'), true)],
        );

        $california = $this->productRepo->addProduct($california);
        $philadelphia = $this->productRepo->addProduct($philadelphia);
        $set = $this->productRepo->addProduct($set);

        // Связки категория–товар с порядком
        $this->categoryRepo->addLink(CategoryProduct::link($rolls->id(), $california->id(), 1));
        $this->categoryRepo->addLink(CategoryProduct::link($rolls->id(), $philadelphia->id(), 2));

        $this->categoryRepo->addLink(CategoryProduct::link($sets->id(), $set->id(), 1));
        $this->categoryRepo->addLink(CategoryProduct::link($sets->id(), $california->id(), 2));
    }

    public function test_catalog_tree_endpoint_returns_categories_with_products(): void
    {
        $response = $this->getJson('/api/catalog');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'categories' => [
                [
                    'category' => [
                        'id',
                        'name',
                        'slug',
                        'sort_order',
                        'is_active',
                        'created_at',
                        'updated_at',
                    ],
                    'products' => [
                        [
                            'id',
                            'name',
                            'description',
                            'status',
                            'nutrition' => [
                                'calories',
                                'proteins',
                                'fats',
                                'carbs',
                                'basis',
                            ],
                            'images',
                            'ingredients',
                            'tags',
                            'prices',
                            'created_at',
                            'updated_at',
                            'archived_at',
                        ],
                    ],
                ],
            ],
        ]);

        $data = $response->json();

        // Проверяем порядок категорий
        $this->assertSame('Роллы', $data['categories'][0]['category']['name']);
        $this->assertSame('Сеты', $data['categories'][1]['category']['name']);

        // Проверяем порядок товаров в категории "Роллы"
        $rollsProducts = $data['categories'][0]['products'];
        $this->assertSame('Калифорния', $rollsProducts[0]['name']);
        $this->assertSame('Филадельфия', $rollsProducts[1]['name']);

        // В "Сетах" первым идёт сет, потом Калифорния
        $setsProducts = $data['categories'][1]['products'];
        $this->assertSame('Сет “Популярный”', $setsProducts[0]['name']);
        $this->assertSame('Калифорния', $setsProducts[1]['name']);
    }
}

