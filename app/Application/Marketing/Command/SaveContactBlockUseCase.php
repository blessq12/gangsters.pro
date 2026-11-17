<?php

namespace App\Application\Marketing\Command;

use App\Application\Marketing\DTO\SaveContactBlockDto;

/**
 * ContactBlock есть в Domain, но репозитория/таблицы пока нет.
 */
final class SaveContactBlockUseCase
{
    /**
     * @return array<string, mixed>
     */
    public function execute(SaveContactBlockDto $dto): array
    {
        unset($dto);

        throw new \InvalidArgumentException('Контактный блок пока без репозитория.');
    }
}
