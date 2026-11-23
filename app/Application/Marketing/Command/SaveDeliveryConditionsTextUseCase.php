<?php

namespace App\Application\Marketing\Command;

use App\Application\Marketing\DTO\SaveDeliveryConditionsTextDto;
use App\Application\Marketing\Presenter\MarketingPresenter;
use App\Domain\Supporting\Marketing\Port\MarketingDeliveryConditionsPort;

final class SaveDeliveryConditionsTextUseCase
{
    public function __construct(
        private readonly MarketingDeliveryConditionsPort $conditions,
        private readonly MarketingPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(SaveDeliveryConditionsTextDto $dto): array
    {
        $saved = $this->conditions->save(
            id: $dto->id,
            body: $dto->body,
            isActive: $dto->isActive,
        );

        return $this->presenter->deliveryConditions($saved);
    }
}
