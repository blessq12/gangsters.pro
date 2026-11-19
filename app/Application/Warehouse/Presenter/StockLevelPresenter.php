<?php

namespace App\Application\Warehouse\Presenter;

use App\Domain\Supporting\Warehouse\Entity\StockLevel;

final class StockLevelPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function present(StockLevel $level): array
    {
        return [
            'ingredient_id' => $level->ingredientId(),
            'quantity' => $level->quantity(),
        ];
    }

    /**
     * @param  list<StockLevel>  $levels
     * @return list<array<string, mixed>>
     */
    public function presentList(array $levels): array
    {
        return array_map(fn (StockLevel $level): array => $this->present($level), $levels);
    }
}
