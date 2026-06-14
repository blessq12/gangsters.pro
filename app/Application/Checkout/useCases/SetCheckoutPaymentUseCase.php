<?php

namespace App\Application\Checkout\useCases;

use App\Application\Checkout\DTO\SetCheckoutPaymentDto;
use RuntimeException;

/**
 * Сценарий: добавить данные оплаты в черновик оформления.
 */
final class SetCheckoutPaymentUseCase
{
    /**
     * @return array<string, mixed>
     */
    public function execute(SetCheckoutPaymentDto $input): array
    {
        throw new RuntimeException('Сценарий не подключён к источнику данных.');
    }
}
