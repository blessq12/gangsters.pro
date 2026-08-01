<?php

namespace App\Domain\Order\Entity;

use DateTimeImmutable;

/**
 * Агрегат заказа — неизменяемый снимок подтверждённого оформления.
 */
final class Order
{
    /**
     * @param  array<string, mixed>  $cart
     * @param  array<string, mixed>  $client
     * @param  array<string, mixed>  $delivery
     * @param  array<string, mixed>  $payment
     */
    private function __construct(
        private ?int $id,
        private readonly string $source,
        private readonly ?string $checkoutId,
        private readonly ?string $partnerCode,
        private readonly ?string $externalOrderId,
        private readonly string $status,
        private readonly array $cart,
        private readonly array $client,
        private readonly array $delivery,
        private readonly array $payment,
        private readonly DateTimeImmutable $createdAt,
    ) {}

    /**
     * @param  array<string, mixed>  $cart
     * @param  array<string, mixed>  $client
     * @param  array<string, mixed>  $delivery
     * @param  array<string, mixed>  $payment
     */
    public static function fromCheckoutSnapshot(
        string $clientRequestId,
        array $cart,
        array $client,
        array $delivery,
        array $payment,
        DateTimeImmutable $createdAt,
    ): self {
        if ($clientRequestId === '') {
            throw new \InvalidArgumentException('Заказ нельзя создать без ссылки на чекаут.');
        }

        $lines = $cart['lines'] ?? null;
        if (! is_array($lines) || $lines === []) {
            throw new \InvalidArgumentException('Заказ нельзя создать с пустой корзиной.');
        }

        return new self(
            id: null,
            source: 'site',
            checkoutId: $clientRequestId,
            partnerCode: null,
            externalOrderId: null,
            status: 'new',
            cart: $cart,
            client: $client,
            delivery: $delivery,
            payment: $payment,
            createdAt: $createdAt,
        );
    }

    /**
     * @param  array<string, mixed>  $cart
     * @param  array<string, mixed>  $client
     * @param  array<string, mixed>  $delivery
     * @param  array<string, mixed>  $payment
     */
    public static function restore(
        int $id,
        string $source,
        ?string $checkoutId,
        ?string $partnerCode,
        ?string $externalOrderId,
        string $status,
        array $cart,
        array $client,
        array $delivery,
        array $payment,
        DateTimeImmutable $createdAt,
    ): self {
        return new self(
            id: $id,
            source: $source,
            checkoutId: $checkoutId,
            partnerCode: $partnerCode,
            externalOrderId: $externalOrderId,
            status: $status,
            cart: $cart,
            client: $client,
            delivery: $delivery,
            payment: $payment,
            createdAt: $createdAt,
        );
    }

    public function id(): int
    {
        if ($this->id === null) {
            throw new \LogicException('Заказ ещё не сохранён.');
        }

        return $this->id;
    }

    public function hasId(): bool
    {
        return $this->id !== null;
    }

    public function assignId(int $id): void
    {
        if ($this->id !== null) {
            throw new \LogicException('id уже назначен.');
        }

        if ($id < 1) {
            throw new \InvalidArgumentException('id должен быть положительным.');
        }

        $this->id = $id;
    }

    public function source(): string
    {
        return $this->source;
    }

    public function checkoutId(): ?string
    {
        return $this->checkoutId;
    }

    public function clientRequestId(): ?string
    {
        return $this->checkoutId;
    }

    public function partnerCode(): ?string
    {
        return $this->partnerCode;
    }

    public function externalOrderId(): ?string
    {
        return $this->externalOrderId;
    }

    public function status(): string
    {
        return $this->status;
    }

    /**
     * @return array<string, mixed>
     */
    public function cart(): array
    {
        return $this->cart;
    }

    /**
     * @return array<string, mixed>
     */
    public function client(): array
    {
        return $this->client;
    }

    /**
     * @return array<string, mixed>
     */
    public function delivery(): array
    {
        return $this->delivery;
    }

    /**
     * @return array<string, mixed>
     */
    public function payment(): array
    {
        return $this->payment;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function clientId(): ?int
    {
        $clientId = $this->client['client_id'] ?? null;

        return $clientId !== null ? (int) $clientId : null;
    }

    public function totalRubles(): int
    {
        $total = 0;

        foreach ($this->cart['lines'] ?? [] as $line) {
            if (! is_array($line)) {
                continue;
            }

            $kind = is_array($line['payload'] ?? null)
                ? (string) (($line['payload']['kind'] ?? '') ?: 'user')
                : 'user';

            if (in_array($kind, ['gift', 'complement'], true)) {
                continue;
            }

            $total += (int) ($line['line_total_rubles'] ?? 0);
        }

        return $total;
    }
}
