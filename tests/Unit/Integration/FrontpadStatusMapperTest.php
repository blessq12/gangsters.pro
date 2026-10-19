<?php

namespace Tests\Unit\Integration;

use App\Domain\Order\Enum\OrderStatus;
use App\Infrastructure\Integration\Frontpad\FrontpadStatusMapper;
use PHPUnit\Framework\TestCase;

final class FrontpadStatusMapperTest extends TestCase
{
    private FrontpadStatusMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mapper = new FrontpadStatusMapper();
    }

    public function test_maps_known_frontpad_statuses_to_domain(): void
    {
        $this->assertSame(OrderStatus::New, $this->mapper->toDomain(1));
        $this->assertSame(OrderStatus::Preorder, $this->mapper->toDomain(13));
        $this->assertSame(OrderStatus::Preparing, $this->mapper->toDomain(3));
        $this->assertSame(OrderStatus::Ready, $this->mapper->toDomain(12));
        $this->assertSame(OrderStatus::InTransit, $this->mapper->toDomain(4));
        $this->assertSame(OrderStatus::Cancelled, $this->mapper->toDomain(11));
        $this->assertSame(OrderStatus::Delivered, $this->mapper->toDomain(10));
    }

    public function test_returns_null_for_unknown_frontpad_status(): void
    {
        $this->assertNull($this->mapper->toDomain(99));
    }
}
