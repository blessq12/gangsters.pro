<?php

namespace Tests\Unit\Application\Operations\Shopping;

use App\Application\Operations\Shopping\DTO\AdminActiveCartListFilters;
use PHPUnit\Framework\TestCase;

final class AdminActiveCartListFiltersTest extends TestCase
{
    public function test_from_search_returns_empty_filters_for_blank_input(): void
    {
        $filters = AdminActiveCartListFilters::fromSearch('   ');

        $this->assertFalse($filters->isActive());
    }

    public function test_from_search_parses_numeric_term_as_session_and_client_id(): void
    {
        $filters = AdminActiveCartListFilters::fromSearch('42');

        $this->assertSame(42, $filters->sessionId);
        $this->assertSame(42, $filters->clientId);
        $this->assertNull($filters->publicId);
        $this->assertNull($filters->orderId);
    }

    public function test_from_search_parses_uuid_as_public_and_order_id(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';

        $filters = AdminActiveCartListFilters::fromSearch($uuid);

        $this->assertNull($filters->sessionId);
        $this->assertNull($filters->clientId);
        $this->assertSame($uuid, $filters->publicId);
        $this->assertSame($uuid, $filters->orderId);
    }
}
