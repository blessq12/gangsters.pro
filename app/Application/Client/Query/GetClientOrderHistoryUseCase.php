<?php

namespace App\Application\Client\Query;

/**
 * Заглушка: история появится после Order Application.
 */
final class GetClientOrderHistoryUseCase
{
    /**
     * @return list<array<string, mixed>>
     */
    public function execute(int $clientId): array
    {
        return [];
    }
}
