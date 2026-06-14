<?php

namespace App\Application\Catalog\useCases;

use RuntimeException;

/**
 * Сценарий: получить каталог для витрины.
 */
final class GetCatalogUseCase
{
    /**
     * @return array{categories: list<array<string, mixed>>}
     */
    public function execute(): array
    {
        throw new RuntimeException('Сценарий не подключён к источнику данных.');
    }
}
