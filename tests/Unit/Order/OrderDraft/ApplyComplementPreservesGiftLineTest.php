<?php

namespace Tests\Unit\Order\OrderDraft;

use App\Application\Order\OrderDraft\DTO\OrderDraftInput;
use App\Application\Order\OrderDraft\Services\BuildOrderDraftFromInput;
use App\Application\Order\OrderDraft\Services\ProcessOrderDraftPipeline;
use App\Application\Order\OrderDraft\Support\PromotionLineClassifier;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ApplyComplementPreservesGiftLineTest extends TestCase
{
    #[Test]
    public function complement_sync_сохраняет_строку_подарка_для_последующего_gift_sync(): void
    {
        $giftProductId = $this->resolveGiftCandidateProductId();
        $regularProductId = $this->resolveRegularProductId();

        if ($giftProductId === null || $regularProductId === null) {
            $this->markTestSkipped('Нет товаров для проверки сохранения подарка.');
        }

        $buildDraft = app(BuildOrderDraftFromInput::class);
        $pipeline = app(ProcessOrderDraftPipeline::class);

        $draft = $buildDraft->build(new OrderDraftInput(
            cartLines: [
                ['product_id' => $regularProductId, 'quantity' => 10, 'payload' => null],
            ],
            selectedGiftProductId: $giftProductId,
            client: ['name' => 'Тест', 'phone' => '+79990001122'],
            delivery: ['method' => 'pickup'],
            payment: ['method' => 'cash'],
        ));

        $draft = $pipeline->process($draft, forPlace: false);

        $giftLines = array_values(array_filter(
            $draft->cart()->lines(),
            static fn ($line): bool => PromotionLineClassifier::isGiftLine($line),
        ));

        $this->assertCount(1, $giftLines);
        $this->assertSame($giftProductId, $giftLines[0]->productId());
        $this->assertSame(0, $giftLines[0]->unitPrice()->amountRubles());
        $this->assertNotEmpty($giftLines[0]->sku());
    }

    private function resolveGiftCandidateProductId(): ?int
    {
        $id = \App\Infrastructure\Catalog\Model\PRD_Product::query()
            ->where('is_system', true)
            ->where('catalog_kind', 'product')
            ->where('status', 'active')
            ->whereNull('archived_at')
            ->value('id');

        return $id !== null ? (int) $id : null;
    }

    private function resolveRegularProductId(): ?int
    {
        $id = \App\Infrastructure\Catalog\Model\PRD_Product::query()
            ->where('is_system', false)
            ->where('catalog_kind', 'product')
            ->where('status', 'active')
            ->whereNull('archived_at')
            ->value('id');

        return $id !== null ? (int) $id : null;
    }
}
