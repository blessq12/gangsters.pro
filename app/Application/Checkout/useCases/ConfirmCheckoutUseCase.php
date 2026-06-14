<?php

namespace App\Application\Checkout\useCases;

use App\Application\Checkout\DTO\ConfirmCheckoutDto;
use App\Application\Checkout\Services\CheckoutDraftLifecycle;
use App\Application\Order\Presenter\OrderPresenter;
use App\Domain\Checkout\Exception\CheckoutNotFoundException;
use App\Domain\Checkout\Repository\CheckoutRepository;
use App\Domain\Checkout\ValueObject\CheckoutId;
use App\Domain\Order\Repository\OrderRepository;

/**
 * Сценарий: финальное применение бенефитов → подтверждение → создание заказа по событию.
 */
final class ConfirmCheckoutUseCase
{
    public function __construct(
        private readonly CheckoutRepository $checkouts,
        private readonly CheckoutDraftLifecycle $draftLifecycle,
        private readonly OrderRepository $orders,
        private readonly OrderPresenter $orderPresenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(ConfirmCheckoutDto $input): array
    {
        $checkout = $this->checkouts->findById(CheckoutId::fromString($input->checkoutId));

        if ($checkout === null) {
            throw CheckoutNotFoundException::forId($input->checkoutId);
        }

        $result = $this->draftLifecycle->confirmAndPresent($checkout);

        $order = $this->orders->findByCheckoutId($input->checkoutId);

        if ($order !== null) {
            $result['order'] = $this->orderPresenter->present($order);
        }

        return $result;
    }
}
