<?php

namespace App\Domain\Crm\Event;

use App\Domain\Crm\Entity\Client;
use DateTimeImmutable;

/** Пароль клиента изменён. */
final readonly class ClientPasswordChanged
{
    public function __construct(
        private int $clientId,
        private ?string $email,
        private string $phone,
        private DateTimeImmutable $occurredAt,
    ) {}

    public static function fromClient(Client $client, ?DateTimeImmutable $occurredAt = null): self
    {
        if (! $client->hasId()) {
            throw new \LogicException('Событие ClientPasswordChanged можно построить только для сохранённого клиента.');
        }

        return new self(
            clientId: $client->id(),
            email: $client->email(),
            phone: $client->phone(),
            occurredAt: $occurredAt ?? new DateTimeImmutable(),
        );
    }

    public function clientId(): int
    {
        return $this->clientId;
    }

    public function email(): ?string
    {
        return $this->email;
    }

    public function phone(): string
    {
        return $this->phone;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
