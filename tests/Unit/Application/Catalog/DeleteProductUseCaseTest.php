<?php

namespace Tests\Unit\Application\Catalog;

use App\Application\Catalog\Command\DeleteProductUseCase;
use App\Application\Catalog\Contracts\ProductDeletionGuardPort;
use App\Application\Common\Exceptions\ApiException;
use App\Domain\Product\Entity\Product;
use App\Domain\Product\Repository\ProductRepository;
use App\Domain\Product\VO\Nutrition;
use Mockery;
use PHPUnit\Framework\TestCase;

final class DeleteProductUseCaseTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_deletes_product_when_guard_allows(): void
    {
        $product = Product::create('Roll', 'desc', new Nutrition(1, 1, 1, 1));
        $product->assignPersistedId(9);

        $repository = Mockery::mock(ProductRepository::class);
        $repository->shouldReceive('findById')->once()->with(9)->andReturn($product);
        $repository->shouldReceive('delete')->once()->with($product);

        $guard = Mockery::mock(ProductDeletionGuardPort::class);
        $guard->shouldReceive('assertDeletable')->once()->with(9);

        $useCase = new DeleteProductUseCase($repository, $guard);
        $useCase->execute(9);

        $this->assertTrue(true);
    }

    public function test_guard_blocks_delete(): void
    {
        $product = Product::create('Roll', 'desc', new Nutrition(1, 1, 1, 1));
        $product->assignPersistedId(9);

        $repository = Mockery::mock(ProductRepository::class);
        $repository->shouldReceive('findById')->once()->with(9)->andReturn($product);
        $repository->shouldNotReceive('delete');

        $guard = Mockery::mock(ProductDeletionGuardPort::class);
        $guard->shouldReceive('assertDeletable')->once()->with(9)
            ->andThrow(new ApiException('blocked', 422));

        $useCase = new DeleteProductUseCase($repository, $guard);

        $this->expectException(ApiException::class);
        $useCase->execute(9);
    }
}
