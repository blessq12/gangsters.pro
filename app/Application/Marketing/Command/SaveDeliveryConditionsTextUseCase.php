<?php

namespace App\Application\Marketing\Command;

use App\Application\Marketing\DTO\SaveDeliveryConditionsTextDto;

/**
 * DeliveryConditionsText есть в Domain, но репозитория/таблицы пока нет.
 */
final class SaveDeliveryConditionsTextUseCase
{
    /**
     * @return array<string, mixed>
     */
    public function execute(SaveDeliveryConditionsTextDto $dto): array
    {
        unset($dto);

        throw new \InvalidArgumentException('Текст условий доставки пока без репозитория.');
    }
}
