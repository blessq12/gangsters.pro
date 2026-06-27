<?php

namespace Tests\Unit\Order\OrderDraft;

use App\Application\Order\OrderDraft\DTO\OrderDraftInput;
use App\Application\Order\OrderDraft\Services\BuildOrderDraftFromInput;
use App\Application\Order\OrderDraft\Services\ProcessOrderDraftPipeline;
use App\Application\Order\OrderDraft\Support\CartRollCounter;
use App\Application\Order\OrderDraft\Support\PromotionLineClassifier;
use App\Domain\Order\OrderDraft\ValueObject\CartLineSnapshot;
use App\Infrastructure\Catalog\Model\PRD_Product;
use App\Shared\ValueObject\Money;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class CartRollCounterSetCompositionTest extends TestCase
{
    #[Test]
    public function набор_из_роллов_учитывается_в_roll_count(): void
    {
        $setId = $this->resolveSetIdBySlug('set-duet');

        if ($setId === null) {
            $this->markTestSkipped('Нет набора set-duet в каталоге.');
        }

        $counter = app(CartRollCounter::class);
        $rollCount = $counter->countRollUnits([
            new CartLineSnapshot(
                productId: $setId,
                productName: 'Набор «Дуэт»',
                quantity: 1,
                unitPrice: Money::rubles(750),
                payload: ['catalog_kind' => 'set'],
            ),
        ]);

        $this->assertSame(2, $rollCount);
    }

    #[Test]
    public function complement_начисляется_при_наборе_в_корзине(): void
    {
        $setId = $this->resolveSetIdBySlug('set-duet');

        if ($setId === null) {
            $this->markTestSkipped('Нет набора set-duet в каталоге.');
        }

        $buildDraft = app(BuildOrderDraftFromInput::class);
        $pipeline = app(ProcessOrderDraftPipeline::class);

        $draft = $buildDraft->build(new OrderDraftInput(
            cartLines: [
                ['product_id' => $setId, 'quantity' => 1, 'payload' => ['catalog_kind' => 'set']],
            ],
            selectedGiftProductId: null,
            client: null,
            delivery: null,
            payment: null,
        ));

        $draft = $pipeline->process($draft, forPlace: false);

        $complementLines = array_values(array_filter(
            $draft->cart()->lines(),
            static fn ($line): bool => PromotionLineClassifier::isComplementLine($line),
        ));

        $this->assertNotEmpty($complementLines);
        foreach ($complementLines as $complementLine) {
            $this->assertNotEmpty($complementLine->sku());
        }
    }

    private function resolveSetIdBySlug(string $slug): ?int
    {
        $id = PRD_Product::query()
            ->where('slug', $slug)
            ->where('catalog_kind', 'set')
            ->where('status', 'active')
            ->whereNull('archived_at')
            ->value('id');

        return $id !== null ? (int) $id : null;
    }
}
