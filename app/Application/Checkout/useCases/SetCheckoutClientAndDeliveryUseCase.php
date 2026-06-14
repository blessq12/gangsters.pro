<?php

namespace App\Application\Checkout\useCases;

use App\Application\Checkout\DTO\SetCheckoutClientAndDeliveryDto;
use RuntimeException;

/**
 * Сценарий: добавить данные клиента и доставки в черновик оформления.
 */
final class SetCheckoutClientAndDeliveryUseCase
{
    /**
     * @return array<string, mixed>
     */
    public function execute(SetCheckoutClientAndDeliveryDto $input): array
    {
        throw new RuntimeException('Сценарий не подключён к источнику данных.');
    }
}
