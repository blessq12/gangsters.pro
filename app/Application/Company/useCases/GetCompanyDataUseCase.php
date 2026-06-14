<?php

namespace App\Application\Company\useCases;

use RuntimeException;

/**
 * Сценарий: получить публичные данные компании.
 */
final class GetCompanyDataUseCase
{
    /**
     * @return array{data: array<string, mixed>|null}
     */
    public function execute(): array
    {
        throw new RuntimeException('Сценарий не подключён к источнику данных.');
    }
}
