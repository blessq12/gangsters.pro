<?php

namespace Tests\Unit\Filament\Operations;

use App\Filament\Operations\Concerns\ConfiguresHubTablePagination;
use App\Filament\Operations\Tables\HubActiveCartsTable;
use App\Filament\Operations\Tables\HubCartRulesProductsTable;
use App\Filament\Operations\Tables\HubClientsTable;
use App\Filament\Operations\Tables\HubOrdersTable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class OperationsHubTablePaginationTest extends TestCase
{
    /**
     * @return array<string, array{class-string}>
     */
    public static function hubTableProvider(): array
    {
        return [
            'orders' => [HubOrdersTable::class],
            'clients' => [HubClientsTable::class],
            'active carts' => [HubActiveCartsTable::class],
            'cart rules products' => [HubCartRulesProductsTable::class],
        ];
    }

    #[DataProvider('hubTableProvider')]
    public function test_hub_table_uses_configures_hub_table_pagination_trait(string $tableClass): void
    {
        $traits = class_uses_recursive($tableClass);

        $this->assertContains(
            ConfiguresHubTablePagination::class,
            $traits,
            $tableClass.' must use ConfiguresHubTablePagination for full pagination UI.',
        );
    }
}
