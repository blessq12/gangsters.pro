<?php

namespace App\Infrastructure\Warehouse\Listener;

use App\Domain\Core\Production\Event\KitchenTicketCompleted;
use App\Domain\Core\Production\Repository\KitchenTicketRepository;
use App\Domain\Supporting\Catalog\Entity\Product;
use App\Domain\Supporting\Catalog\Entity\ProductSet;
use App\Domain\Supporting\Catalog\Repository\CatalogItemRepository;
use App\Domain\Supporting\Warehouse\Entity\StockLevel;
use App\Domain\Supporting\Warehouse\Entity\StockWriteOff;
use App\Domain\Supporting\Warehouse\Repository\IngredientRepository;
use App\Domain\Supporting\Warehouse\Repository\StockLevelRepository;
use App\Domain\Supporting\Warehouse\Repository\StockWriteOffRepository;
use Illuminate\Support\Facades\Log;

/**
 * Готовка завершена → списание ингредиентов по «карте» Product.ingredients (имена).
 * Количеств в карте нет: на 1 шт позиции списываем 1.0 ед. склада на каждый найденный ингредиент.
 * Нет рецепта / нет ингредиента на складе / нулевой остаток — логируем и не падаем.
 */
final class WriteOffIngredientsOnKitchenTicketCompleted
{
    public function __construct(
        private readonly KitchenTicketRepository $tickets,
        private readonly CatalogItemRepository $catalog,
        private readonly IngredientRepository $ingredients,
        private readonly StockLevelRepository $levels,
        private readonly StockWriteOffRepository $writeOffs,
    ) {}

    public function handle(KitchenTicketCompleted $event): void
    {
        $ticket = $this->tickets->findByOrderId($event->orderId);
        if ($ticket === null || $ticket->id() !== $event->ticketId) {
            Log::info('Warehouse write-off skipped: ticket not found', [
                'order_id' => $event->orderId,
                'ticket_id' => $event->ticketId,
            ]);

            return;
        }

        /** @var array<int, float> $neededByIngredientId */
        $neededByIngredientId = [];

        foreach ($ticket->lines() as $line) {
            $recipe = $this->resolveRecipe($line->productId(), $line->quantity());
            if ($recipe === []) {
                Log::info('Warehouse write-off skipped: no recipe for catalog item', [
                    'ticket_id' => $event->ticketId,
                    'product_id' => $line->productId(),
                ]);

                continue;
            }

            foreach ($recipe as $ingredientName => $qty) {
                $ingredient = $this->ingredients->findByName($ingredientName);
                if ($ingredient === null) {
                    Log::info('Warehouse write-off skipped: ingredient not in warehouse', [
                        'ticket_id' => $event->ticketId,
                        'ingredient_name' => $ingredientName,
                        'product_id' => $line->productId(),
                    ]);

                    continue;
                }

                $ingredientId = $ingredient->id();
                $neededByIngredientId[$ingredientId] = ($neededByIngredientId[$ingredientId] ?? 0.0) + $qty;
            }
        }

        foreach ($neededByIngredientId as $ingredientId => $needed) {
            $this->writeOffAvailable(
                ingredientId: $ingredientId,
                needed: $needed,
                ticketId: $event->ticketId,
            );
        }
    }

    /**
     * Карта: имя ингредиента → количество к списанию.
     * В Product.ingredients только имена без норм — 1.0 ед. склада на 1 шт позиции.
     *
     * @return array<string, float>
     */
    private function resolveRecipe(int $catalogItemId, int $lineQuantity): array
    {
        $product = $this->catalog->findProductById($catalogItemId);
        if ($product instanceof Product) {
            return $this->namesToQty($product->ingredients(), (float) $lineQuantity);
        }

        $set = $this->catalog->findSetById($catalogItemId);
        if (! $set instanceof ProductSet) {
            return [];
        }

        $recipe = [];
        foreach ($set->lines() as $setLine) {
            $member = $this->catalog->findProductById($setLine->productId());
            if (! $member instanceof Product) {
                Log::info('Warehouse write-off skipped: set member product missing', [
                    'set_id' => $catalogItemId,
                    'product_id' => $setLine->productId(),
                ]);

                continue;
            }

            $qty = (float) ($lineQuantity * $setLine->quantity());
            foreach ($this->namesToQty($member->ingredients(), $qty) as $name => $amount) {
                $recipe[$name] = ($recipe[$name] ?? 0.0) + $amount;
            }
        }

        return $recipe;
    }

    /**
     * @param  list<string>  $names
     * @return array<string, float>
     */
    private function namesToQty(array $names, float $quantity): array
    {
        $recipe = [];
        foreach ($names as $name) {
            $name = trim($name);
            if ($name === '') {
                continue;
            }
            $recipe[$name] = ($recipe[$name] ?? 0.0) + $quantity;
        }

        return $recipe;
    }

    private function writeOffAvailable(int $ingredientId, float $needed, int $ticketId): void
    {
        $current = $this->levels->findByIngredientId($ingredientId);
        $available = $current?->quantity() ?? 0.0;

        if ($available <= 0) {
            Log::info('Warehouse write-off skipped: zero stock', [
                'ticket_id' => $ticketId,
                'ingredient_id' => $ingredientId,
                'needed' => $needed,
            ]);

            return;
        }

        $quantity = min($available, $needed);
        if ($quantity < $needed) {
            Log::info('Warehouse write-off partial: insufficient stock', [
                'ticket_id' => $ticketId,
                'ingredient_id' => $ingredientId,
                'needed' => $needed,
                'written_off' => $quantity,
            ]);
        }

        $writeOff = StockWriteOff::record(
            ingredientId: $ingredientId,
            quantity: $quantity,
            reason: 'Списание по завершению кухонного тикета #'.$ticketId,
            kitchenTicketId: $ticketId,
        );
        $this->writeOffs->save($writeOff);

        $this->levels->save(new StockLevel(
            ingredientId: $ingredientId,
            quantity: $available - $quantity,
        ));
    }
}
