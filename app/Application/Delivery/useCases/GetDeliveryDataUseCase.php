<?php

namespace App\Application\Delivery\useCases;

use RuntimeException;

/**
 * Сценарий: получить публичные данные доставки.
 */
final class GetDeliveryDataUseCase
{
    /**
     * @return array{data: array<string, mixed>|null}
     */
    public function execute(): array
    {
        throw new RuntimeException('Сценарий не подключён к источнику данных.');
    }
}
