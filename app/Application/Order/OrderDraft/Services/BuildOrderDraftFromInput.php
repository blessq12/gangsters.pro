<?php

namespace App\Application\Order\OrderDraft\Services;

use App\Application\Order\OrderDraft\DTO\OrderDraftInput;
use App\Domain\Order\OrderDraft\Entity\OrderDraft;
use App\Domain\Order\OrderDraft\ValueObject\CartLineSnapshot;
use App\Domain\Order\OrderDraft\ValueObject\CartSnapshot;
use App\Domain\Order\OrderDraft\ValueObject\ClientSnapshot;
use App\Domain\Order\OrderDraft\ValueObject\DeliveryAddress;
use App\Domain\Order\OrderDraft\ValueObject\DeliverySnapshot;
use App\Domain\Order\OrderDraft\ValueObject\GuestContact;
use App\Domain\Order\OrderDraft\ValueObject\PaymentSnapshot;
use App\Domain\Order\Port\CatalogPricingPort;
use App\Domain\Order\Port\ClientProfilePort;
use App\Domain\Client\ValueObject\PhoneNumber;
use App\Shared\Enum\DeliveryMethod;
use App\Shared\Enum\PaymentMethod;
use InvalidArgumentException;

/**
 * Сборка in-memory OrderDraft из JSON запроса.
 */
final class BuildOrderDraftFromInput
{
    public function __construct(
        private readonly CatalogPricingPort $pricing,
        private readonly ClientProfilePort $clientProfiles,
    ) {}

    public function build(OrderDraftInput $input): OrderDraft
    {
        $draft = OrderDraft::empty();
        $draft->setCart($this->buildCart($input));

        $client = $this->buildClient($input->client);
        if ($client instanceof ClientSnapshot) {
            $draft->setClient($client);
        }

        $delivery = $this->buildDelivery($input->delivery);
        if ($delivery instanceof DeliverySnapshot) {
            $draft->setDelivery($delivery);
        }

        $payment = $this->buildPayment($input->payment);
        if ($payment instanceof PaymentSnapshot) {
            $draft->setPayment($payment);
        }

        return $draft;
    }

    private function buildCart(OrderDraftInput $input): CartSnapshot
    {
        $lines = [];

        foreach ($input->cartLines as $row) {
            $payload = is_array($row['payload'] ?? null) ? $row['payload'] : null;
            $kind = is_array($payload) ? ($payload['kind'] ?? 'user') : 'user';

            if (in_array($kind, ['gift', 'complement'], true)) {
                continue;
            }

            $productId = (int) $row['product_id'];
            $quantity = (int) $row['quantity'];

            if ($quantity < 1) {
                continue;
            }

            $quote = $this->pricing->findStorefrontProductQuote($productId);

            if ($quote === null) {
                throw new InvalidArgumentException('Товар недоступен для добавления в корзину.');
            }

            $lines[] = CartLineSnapshot::fromQuote($quote, $quantity, $payload);
        }

        if ($input->selectedGiftProductId !== null) {
            $giftQuote = $this->pricing->findActiveProductQuote($input->selectedGiftProductId);

            if ($giftQuote !== null) {
                $lines[] = new CartLineSnapshot(
                    productId: $giftQuote->productId(),
                    productName: $giftQuote->productName(),
                    quantity: 1,
                    unitPrice: $giftQuote->unitPrice(),
                    payload: ['kind' => 'gift'],
                    sku: $giftQuote->sku(),
                );
            }
        }

        return CartSnapshot::fromLines($lines);
    }

    /**
     * @param  array<string, mixed>|null  $client
     */
    private function buildClient(?array $client): ?ClientSnapshot
    {
        if ($client === null) {
            return null;
        }

        $clientId = isset($client['client_id']) ? (int) $client['client_id'] : null;

        if ($clientId !== null && $clientId > 0) {
            $profile = $this->clientProfiles->findRegisteredProfile($clientId);

            if ($profile === null) {
                throw new InvalidArgumentException('Клиент с указанным идентификатором не найден.');
            }

            return ClientSnapshot::registered(
                clientId: $clientId,
                name: isset($client['name']) ? (string) $client['name'] : $profile->name(),
                phone: isset($client['phone']) && trim((string) $client['phone']) !== ''
                    ? PhoneNumber::formatFromRaw((string) $client['phone'])
                    : $profile->phone(),
                email: isset($client['email']) ? (string) $client['email'] : $profile->email(),
            );
        }

        $name = isset($client['name']) ? trim((string) $client['name']) : '';
        $phoneRaw = isset($client['phone']) ? trim((string) $client['phone']) : '';

        if ($name === '' || $phoneRaw === '') {
            return null;
        }

        return ClientSnapshot::guest(
            new GuestContact(
                name: $name,
                phone: PhoneNumber::formatFromRaw($phoneRaw),
                email: isset($client['email']) ? (string) $client['email'] : null,
            ),
        );
    }

    /**
     * @param  array<string, mixed>|null  $delivery
     */
    private function buildDelivery(?array $delivery): ?DeliverySnapshot
    {
        if ($delivery === null || ! isset($delivery['method'])) {
            return null;
        }

        $method = DeliveryMethod::from((string) $delivery['method']);
        $addressPayload = $delivery['address'] ?? null;

        $address = is_array($addressPayload)
            ? new DeliveryAddress(
                street: (string) ($addressPayload['street'] ?? ''),
                house: (string) ($addressPayload['house'] ?? ''),
                entrance: isset($addressPayload['entrance']) ? (string) $addressPayload['entrance'] : null,
                apartment: isset($addressPayload['apartment']) ? (string) $addressPayload['apartment'] : null,
                latitude: self::nullableCoordinate($addressPayload['latitude'] ?? null),
                longitude: self::nullableCoordinate($addressPayload['longitude'] ?? null),
            )
            : null;

        return new DeliverySnapshot(
            method: $method,
            address: $address,
            comment: isset($delivery['comment']) ? (string) $delivery['comment'] : null,
            scheduledAt: isset($delivery['scheduled_at']) ? (string) $delivery['scheduled_at'] : null,
        );
    }

    /**
     * @param  array<string, mixed>|null  $payment
     */
    private function buildPayment(?array $payment): ?PaymentSnapshot
    {
        if ($payment === null || ! isset($payment['method'])) {
            return null;
        }

        return new PaymentSnapshot(
            method: PaymentMethod::from((string) $payment['method']),
            changeFromRubles: isset($payment['change_from_rubles']) ? (int) $payment['change_from_rubles'] : null,
        );
    }

    private static function nullableCoordinate(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value) && trim($value) === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        $float = (float) $value;

        return is_finite($float) ? $float : null;
    }
}
