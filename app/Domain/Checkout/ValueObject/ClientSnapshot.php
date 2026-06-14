<?php

namespace App\Domain\Checkout\ValueObject;

use App\Shared\Enum\ClientKind;

final readonly class ClientSnapshot
{
    private function __construct(
        private ClientKind $kind,
        private ?int $clientId,
        private ?GuestContact $guestContact,
        private ?string $name,
        private ?string $phone,
        private ?string $email,
    ) {}

    public static function guest(GuestContact $contact): self
    {
        return new self(
            kind: ClientKind::Guest,
            clientId: null,
            guestContact: $contact,
            name: $contact->name(),
            phone: $contact->phone(),
            email: $contact->email(),
        );
    }

    public static function registered(int $clientId, ?string $name = null, ?string $phone = null, ?string $email = null): self
    {
        return new self(
            kind: ClientKind::Registered,
            clientId: $clientId,
            guestContact: null,
            name: $name,
            phone: $phone,
            email: $email,
        );
    }

    public function kind(): ClientKind
    {
        return $this->kind;
    }

    public function clientId(): ?int
    {
        return $this->clientId;
    }

    public function guestContact(): ?GuestContact
    {
        return $this->guestContact;
    }

    public function name(): ?string
    {
        return $this->name;
    }

    public function phone(): ?string
    {
        return $this->phone;
    }

    public function email(): ?string
    {
        return $this->email;
    }
}
