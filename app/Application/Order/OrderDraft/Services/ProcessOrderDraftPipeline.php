<?php

namespace App\Application\Order\OrderDraft\Services;

use App\Domain\Order\OrderDraft\Entity\OrderDraft;
use App\Domain\Order\OrderDraft\ValueObject\DeliverySnapshot;

/**
 * Pipeline обработки черновика: benefits sync → geocode → validate.
 */
final class ProcessOrderDraftPipeline
{
    public function __construct(
        private readonly ApplyComplementBenefitLines $complementBenefitLines,
        private readonly ApplyGiftBenefitLines $giftBenefitLines,
        private readonly PrepareOrderDraftDeliveryAddress $prepareDeliveryAddress,
    ) {}

    public function process(OrderDraft $draft, bool $forPlace): OrderDraft
    {
        $this->applyPromotionLines($draft);
        $this->applyPreparedDelivery($draft);

        if ($forPlace) {
            $draft->assertReadyForPlace();
            $this->giftBenefitLines->assertValidForPlace($draft);
        }

        return $draft;
    }

    private function applyPromotionLines(OrderDraft $draft): void
    {
        $this->complementBenefitLines->sync($draft);
        $this->giftBenefitLines->sync($draft);
    }

    private function applyPreparedDelivery(OrderDraft $draft): void
    {
        $delivery = $draft->delivery();

        if (! $delivery instanceof DeliverySnapshot) {
            return;
        }

        $preparedAddress = $this->prepareDeliveryAddress->prepare(
            method: $delivery->method(),
            address: $delivery->address(),
        );

        $draft->setDelivery(
            new DeliverySnapshot(
                method: $delivery->method(),
                address: $preparedAddress,
                comment: $delivery->comment(),
                scheduledAt: $delivery->scheduledAt(),
            ),
        );
    }
}
