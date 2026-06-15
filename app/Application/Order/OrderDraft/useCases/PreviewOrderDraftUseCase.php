<?php

namespace App\Application\Order\OrderDraft\useCases;

use App\Application\Order\OrderDraft\DTO\OrderDraftInput;
use App\Application\Order\OrderDraft\Presenter\OrderDraftPresenter;
use App\Application\Order\OrderDraft\Services\BuildOrderDraftFromInput;
use App\Application\Order\OrderDraft\Services\ProcessOrderDraftPipeline;

/**
 * Сценарий: stateless preview черновика заказа.
 */
final class PreviewOrderDraftUseCase
{
    public function __construct(
        private readonly BuildOrderDraftFromInput $buildDraft,
        private readonly ProcessOrderDraftPipeline $pipeline,
        private readonly OrderDraftPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(OrderDraftInput $input): array
    {
        $draft = $this->buildDraft->build($input);
        $draft = $this->pipeline->process($draft, forPlace: false);

        return $this->presenter->present($draft);
    }
}
