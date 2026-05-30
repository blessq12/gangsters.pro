<?php

namespace Tests\Unit\Application\Operations\Shopping;

use App\Application\Operations\Client\Contracts\AdminClientReadRepository;
use App\Application\Operations\Shopping\Contracts\AdminShoppingProductReadRepository;
use App\Application\Operations\Shopping\Contracts\AdminShoppingSessionReadRepository;
use App\Application\Operations\Shopping\Presenter\AdminShoppingSessionPresenter;
use App\Application\Operations\Shopping\Query\GetAdminShoppingSessionListQuery;
use PHPUnit\Framework\TestCase;

final class GetAdminShoppingSessionListQueryTest extends TestCase
{
    public function test_maps_read_repository_rows_to_list_items(): void
    {
        $readRepo = $this->createMock(AdminShoppingSessionReadRepository::class);
        $readRepo->method('paginateActiveCarts')->with(2, 10)->willReturn([
            'items' => [[
                'id' => 7,
                'public_id' => 'pub-7',
                'client_id' => null,
                'client_label' => 'Гость',
                'cart_lines_count' => 3,
                'favorites_count' => 1,
                'updated_at' => '2026-01-02T10:00:00+00:00',
                'expires_at' => '2026-01-09T10:00:00+00:00',
            ]],
            'total' => 15,
        ]);

        $query = new GetAdminShoppingSessionListQuery(
            $readRepo,
            new AdminShoppingSessionPresenter(
                $this->createMock(AdminShoppingProductReadRepository::class),
                $this->createMock(AdminClientReadRepository::class),
            ),
        );

        $result = $query->execute(page: 2, perPage: 10);

        $this->assertSame(15, $result['total']);
        $this->assertSame(2, $result['page']);
        $this->assertSame(10, $result['per_page']);
        $this->assertSame(7, $result['items'][0]['id']);
        $this->assertSame('Гость', $result['items'][0]['client_label']);
        $this->assertSame(3, $result['items'][0]['cart_lines_count']);
    }
}
