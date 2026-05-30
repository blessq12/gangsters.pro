<?php

namespace Tests\Unit\Application\Catalog;

use App\Application\Catalog\Command\ArchiveProductUseCase;
use App\Application\Catalog\Presenter\AdminProductPresenter;
use App\Application\Catalog\Support\CatalogEventPublisher;
use App\Domain\Product\Entity\Product;
use App\Domain\Product\Repository\ProductRepository;
use App\Domain\Product\VO\Nutrition;
use App\Shared\Events\DomainEventBus;
use Mockery;
use PHPUnit\Framework\TestCase;

final class ArchiveProductUseCaseTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_archives_active_product(): void
    {
        $product = Product::create('Roll', 'desc', new Nutrition(1, 1, 1, 1));
        $product->assignPersistedId(7);

        $repository = Mockery::mock(ProductRepository::class);
        $repository->shouldReceive('findById')->once()->with(7)->andReturn($product);
        $repository->shouldReceive('save')->once()->with($product);

        $events = Mockery::mock(DomainEventBus::class);
        $events->shouldReceive('publish')->once();

        $useCase = new ArchiveProductUseCase(
            $repository,
            new AdminProductPresenter,
            new CatalogEventPublisher($events),
        );

        $result = $useCase->execute(7);

        $this->assertSame(Product::STATUS_ARCHIVED, $result['status']);
        $this->assertSame(Product::STATUS_ARCHIVED, $product->status());
    }
}
