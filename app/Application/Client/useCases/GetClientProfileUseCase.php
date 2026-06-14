<?php

namespace App\Application\Client\useCases;

use RuntimeException;

/**
 * Сценарий: получить данные авторизованного клиента.
 */
final class GetClientProfileUseCase
{
    /**
     * @return array{client: array<string, mixed>}
     */
    public function execute(int $clientId): array
    {
        throw new RuntimeException('Сценарий не подключён к источнику данных.');
    }
}
