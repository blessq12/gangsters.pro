<?php

namespace Tests\Unit\Application\Catalog;

use App\Application\Catalog\Presenter\AdminProductPresenter;
use App\Application\Catalog\Query\GetAdminProductFormQuery;
use App\Application\Common\Exceptions\ApiException;
use App\Domain\Product\Entity\Product;
use App\Domain\Product\ReadModel\ProductAdminFormReadModel;
use App\Domain\Product\Repository\ProductRepository;
use App\Domain\Product\VO\Nutrition;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class GetAdminProductFormQueryTest extends TestCase
{
    #[Test]
    public function execute_returns_form_detail_without_loading_images(): void
    {
        $product = Product::reconstitute(
            id: 1,
            name: 'Ролл',
            articul: 'A-1',
            description: 'Описание',
            nutrition: new Nutrition(100, 5, 2, 10, 'per_100g'),
            images: [],
            ingredients: [],
            tags: [],
            cartRuleCountsAsRoll: false,
            cartRuleGiftCandidate: false,
            cartRuleIsComplementSet: false,
            price: 35000,
            status: Product::STATUS_ACTIVE,
            createdAt: new DateTimeImmutable,
            updatedAt: new DateTimeImmutable,
            archivedAt: null,
        );

        $readModel = new ProductAdminFormReadModel(
            product: $product,
            slug: 'roll',
            imagesCount: 3,
        );

        $repository = $this->createMock(ProductRepository::class);
        $repository->method('findByIdForAdminForm')->with(1)->willReturn($readModel);
        $repository->expects($this->never())->method('findById');

        $result = (new GetAdminProductFormQuery($repository, new AdminProductPresenter))->execute(1);

        $this->assertSame('Ролл', $result['name']);
        $this->assertSame('roll', $result['slug']);
        $this->assertSame(3, $result['images_count']);
    }

    #[Test]
    public function execute_throws_when_product_missing(): void
    {
        $repository = $this->createMock(ProductRepository::class);
        $repository->method('findByIdForAdminForm')->with(99)->willReturn(null);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Product not found.');

        (new GetAdminProductFormQuery($repository, new AdminProductPresenter))->execute(99);
    }
}
