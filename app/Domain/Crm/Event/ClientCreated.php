<?php

namespace App\Domain\Crm\Event;

use App\Domain\Crm\Entity\Client;
use DateTimeImmutable;

/** Клиент зарегистрирован. */
final readonly class ClientCreated
{
    public function __construct(
        private int $clientId,
        private string $name,
        private string $phone,
        private ?string $email,
        private DateTimeImmutable $occurredAt,
    ) {}

    public static function fromClient(Client $client): self
    {
        if (! $client->hasId()) {
            throw new \LogicException('Событие ClientCreated можно построить только после сохранения клиента.');
        }

        return new self(
            clientId: $client->id(),
            name: $client->name(),
            phone: $client->phone(),
            email: $client->email(),
            occurredAt: $client->createdAt(),
        );
    }

    public function clientId(): int
    {
        return $this->clientId;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function phone(): string
    {
        return $this->phone;
    }

    public function email(): ?string
    {
        return $this->email;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
