<?php

namespace App\Application\Checkout\useCases;

use App\Application\Checkout\DTO\UpdateCartDto;
use RuntimeException;

/**
 * Сценарий: обновить корзину.
 *
 * Одна операция за вызов: добавить, удалить или изменить количество товара.
 */
final class UpdateCartUseCase
{
    /**
     * @return array<string, mixed>
     */
    public function execute(UpdateCartDto $input): array
    {
        throw new RuntimeException('Сценарий не подключён к источнику данных.');
    }
}
