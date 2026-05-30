<?php

namespace Tests\Unit\Application\Catalog;

use App\Application\Catalog\Command\SetCategoryProductsUseCase;
use App\Application\Catalog\DTO\SetCategoryProductsDTO;
use App\Application\Catalog\Presenter\AdminProductPresenter;
use App\Application\Catalog\Query\GetAdminCategoryLayoutQuery;
use App\Domain\Category\Entity\Category;
use App\Domain\Category\Entity\CategoryProduct;
use App\Domain\Category\Repository\CategoryRepository;
use App\Domain\Product\Repository\ProductRepository;
use Mockery;
use PHPUnit\Framework\TestCase;

final class SetCategoryProductsUseCaseTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_replaces_layout_links(): void
    {
        $category = Category::create('Суши', 'sushi', 1, true);
        $ref = new \ReflectionProperty($category, 'id');
        $ref->setAccessible(true);
        $ref->setValue($category, 5);

        $oldLink = CategoryProduct::link(5, 10, 0);
        $refLink = new \ReflectionProperty($oldLink, 'id');
        $refLink->setAccessible(true);
        $refLink->setValue($oldLink, 99);

        $categories = Mockery::mock(CategoryRepository::class);
        $categories->shouldReceive('findById')->twice()->with(5)->andReturn($category);
        $categories->shouldReceive('findLinksByCategoryId')->twice()->with(5)->andReturn([$oldLink], []);
        $categories->shouldReceive('deleteLink')->once()->with($oldLink);
        $categories->shouldReceive('saveLink')->once()->with(Mockery::on(
            static fn (CategoryProduct $link): bool => $link->categoryId() === 5
                && $link->productId() === 20
                && $link->sortOrder() === 0,
        ));

        $products = Mockery::mock(ProductRepository::class);
        $products->shouldReceive('findByIds')->andReturn([]);

        $layout = new GetAdminCategoryLayoutQuery($categories, $products, new AdminProductPresenter);

        $useCase = new SetCategoryProductsUseCase($categories, $layout);
        $result = $useCase->execute(new SetCategoryProductsDTO(5, [20]));

        $this->assertSame(5, $result['category_id']);
    }
}
