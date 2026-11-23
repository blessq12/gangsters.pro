<?php

namespace App\Application\Client\Query;

/**
 * Заглушка: repeatable lines появятся после Order Application.
 */
final class GetRepeatableOrderLinesUseCase
{
    /**
     * @return array{lines: list<array<string, mixed>>}
     */
    public function execute(int $clientId, int $orderId): array
    {
        return ['lines' => []];
    }
}
