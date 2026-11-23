<?php

namespace App\Infrastructure\Warehouse\Listener;

use App\Domain\Core\Production\Event\KitchenTicketCompleted;
use App\Domain\Supporting\Warehouse\Entity\Ingredient;
use App\Domain\Supporting\Warehouse\Entity\StockLevel;
use App\Domain\Supporting\Warehouse\Entity\StockWriteOff;
use App\Domain\Supporting\Warehouse\Port\KitchenTicketSnapshotPort;
use App\Domain\Supporting\Warehouse\Port\ProductRecipeSnapshotPort;
use App\Domain\Supporting\Warehouse\Repository\IngredientRepository;
use App\Domain\Supporting\Warehouse\Repository\StockLevelRepository;
use App\Domain\Supporting\Warehouse\Repository\StockWriteOffRepository;
use Illuminate\Support\Facades\Log;

/**
 * Завершение тикета → списание по техкартам со склада филиала тикета.
 */
final class WriteOffStockOnKitchenTicketCompleted
{
    public function __construct(
        private readonly KitchenTicketSnapshotPort $tickets,
        private readonly ProductRecipeSnapshotPort $recipes,
        private readonly IngredientRepository $ingredients,
        private readonly StockLevelRepository $levels,
        private readonly StockWriteOffRepository $writeOffs,
    ) {}

    public function handle(KitchenTicketCompleted $event): void
    {
        $snapshot = $this->tickets->findCompletedTicket($event->ticketId, $event->orderId);
        if ($snapshot === null) {
            Log::info('Warehouse write-off skipped: ticket snapshot missing', [
                'order_id' => $event->orderId,
                'ticket_id' => $event->ticketId,
            ]);

            return;
        }

        $branchId = (int) $snapshot['branch_id'];
        $needs = $this->recipes->resolveNeeds($branchId, $snapshot['lines'] ?? []);

        /** @var array<int, float> $neededByIngredientId */
        $neededByIngredientId = [];

        foreach ($needs as $need) {
            $ref = trim((string) ($need['ingredient_ref'] ?? ''));
            $qty = (float) ($need['quantity'] ?? 0);
            if ($ref === '' || $qty <= 0) {
                continue;
            }

            $ingredient = $this->resolveIngredient($ref);
            if ($ingredient === null) {
                Log::info('Warehouse write-off skipped: ingredient_ref not found', [
                    'ticket_id' => $event->ticketId,
                    'ingredient_ref' => $ref,
                ]);

                continue;
            }

            $ingredientId = $ingredient->id();
            $neededByIngredientId[$ingredientId] = ($neededByIngredientId[$ingredientId] ?? 0.0) + $qty;
        }

        foreach ($neededByIngredientId as $ingredientId => $needed) {
            $this->writeOffAvailable(
                branchId: $branchId,
                ingredientId: $ingredientId,
                needed: $needed,
                ticketId: $event->ticketId,
            );
        }
    }

    private function resolveIngredient(string $ingredientRef): ?Ingredient
    {
        if (! ctype_digit($ingredientRef)) {
            return null;
        }

        return $this->ingredients->findById((int) $ingredientRef);
    }

    private function writeOffAvailable(int $branchId, int $ingredientId, float $needed, int $ticketId): void
    {
        $current = $this->levels->findByBranchAndIngredient($branchId, $ingredientId);
        $available = $current?->quantity() ?? 0.0;

        if ($available <= 0) {
            Log::info('Warehouse write-off skipped: zero stock', [
                'ticket_id' => $ticketId,
                'branch_id' => $branchId,
                'ingredient_id' => $ingredientId,
                'needed' => $needed,
            ]);

            return;
        }

        $quantity = min($available, $needed);
        if ($quantity < $needed) {
            Log::info('Warehouse write-off partial: insufficient stock', [
                'ticket_id' => $ticketId,
                'branch_id' => $branchId,
                'ingredient_id' => $ingredientId,
                'needed' => $needed,
                'written_off' => $quantity,
            ]);
        }

        $writeOff = StockWriteOff::record(
            branchId: $branchId,
            ingredientId: $ingredientId,
            quantity: $quantity,
            reason: 'Списание по завершению кухонного тикета #'.$ticketId,
            kitchenTicketId: $ticketId,
        );
        $this->writeOffs->save($writeOff);

        $this->levels->save(new StockLevel(
            branchId: $branchId,
            ingredientId: $ingredientId,
            quantity: $available - $quantity,
        ));
    }
}
