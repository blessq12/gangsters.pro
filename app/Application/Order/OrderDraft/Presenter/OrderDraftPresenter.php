<?php

namespace App\Application\Order\OrderDraft\Presenter;

use App\Application\Order\OrderDraft\Services\EvaluateOrderDraftBenefits;
use App\Application\Order\OrderDraft\Support\OrderDraftWizardResolver;
use App\Domain\Order\OrderDraft\Entity\OrderDraft;
use App\Domain\Order\OrderDraft\ValueObject\CartLineSnapshot;
use App\Domain\Order\OrderDraft\ValueObject\ClientSnapshot;
use App\Domain\Order\OrderDraft\ValueObject\DeliveryAddress;
use App\Domain\Order\OrderDraft\ValueObject\DeliverySnapshot;
use App\Domain\Order\OrderDraft\ValueObject\PaymentSnapshot;

final class OrderDraftPresenter
{
    public function __construct(
        private readonly EvaluateOrderDraftBenefits $evaluateBenefits,
        private readonly OrderDraftWizardResolver $wizardResolver,
        private readonly OrderDraftOrderPreviewPresenter $orderPreviewPresenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function present(OrderDraft $draft): array
    {
        $benefits = $this->evaluateBenefits->evaluate($draft);

        return [
            'cart' => $this->presentCart($draft, $benefits['promo_state']),
            'client' => $draft->client() instanceof ClientSnapshot
                ? $this->presentClient($draft->client())
                : null,
            'delivery' => $draft->delivery() instanceof DeliverySnapshot
                ? $this->presentDelivery($draft->delivery())
                : null,
            'payment' => $draft->payment() instanceof PaymentSnapshot
                ? $this->presentPayment($draft->payment())
                : null,
            'benefits_progress' => $benefits['benefits_progress'],
            'delivery_pricing' => $benefits['delivery_pricing'],
            'promo_state' => $benefits['promo_state'],
            'wizard' => $this->wizardResolver->resolve($draft),
            'order_preview' => $this->orderPreviewPresenter->present($draft, $benefits),
        ];
    }

    /**
     * @param  array<string, mixed>  $promoState
     * @return array<string, mixed>
     */
    private function presentCart(OrderDraft $draft, array $promoState): array
    {
        return [
            'items' => array_map(
                fn (CartLineSnapshot $line): array => [
                    'product_id' => $line->productId(),
                    'product_name' => $line->productName(),
                    'quantity' => $line->quantity(),
                    'unit_price_rubles' => $line->unitPrice()->amountRubles(),
                    'line_total_rubles' => $line->lineTotal()->amountRubles(),
                    'payload' => $line->payload(),
                ],
                $draft->cart()->lines(),
            ),
            'items_total_rubles' => $draft->cart()->itemsTotal()->amountRubles(),
            'payable_total_rubles' => $draft->cart()->payableTotal()->amountRubles(),
            'promo_state' => $promoState,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function presentClient(ClientSnapshot $client): array
    {
        return [
            'kind' => $client->kind()->value,
            'client_id' => $client->clientId(),
            'name' => $client->name(),
            'phone' => $client->phone(),
            'email' => $client->email(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function presentDelivery(DeliverySnapshot $delivery): array
    {
        $address = $delivery->address();

        return [
            'method' => $delivery->method()->value,
            'address' => $address instanceof DeliveryAddress
                ? [
                    'street' => $address->street(),
                    'house' => $address->house(),
                    'entrance' => $address->entrance(),
                    'apartment' => $address->apartment(),
                    'latitude' => $address->latitude(),
                    'longitude' => $address->longitude(),
                ]
                : null,
            'comment' => $delivery->comment(),
            'scheduled_at' => $delivery->scheduledAt(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function presentPayment(PaymentSnapshot $payment): array
    {
        return [
            'method' => $payment->method()->value,
            'change_from_rubles' => $payment->changeFromRubles(),
        ];
    }
}
