<?php

namespace App\Domain\Order\ValueObject;

use App\Domain\Order\Enum\OrderClientKind;

final readonly class OrderClientSnapshot
{
    private function __construct(
        private OrderClientKind $kind,
        private ?int $clientId,
        private ?OrderGuestContact $guestContact,
        private ?string $name,
        private ?string $phone,
        private ?string $email,
    ) {}

    public static function guest(OrderGuestContact $contact): self
    {
        return new self(
            kind: OrderClientKind::Guest,
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
            kind: OrderClientKind::Registered,
            clientId: $clientId,
            guestContact: null,
            name: $name,
            phone: $phone,
            email: $email,
        );
    }

    public function kind(): OrderClientKind
    {
        return $this->kind;
    }

    public function clientId(): ?int
    {
        return $this->clientId;
    }

    public function guestContact(): ?OrderGuestContact
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
