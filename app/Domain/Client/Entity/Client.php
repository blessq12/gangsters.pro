<?php

namespace App\Domain\Client\Entity;

use App\Domain\Client\Event\ClientRegistered;
use App\Domain\Client\Exception\ClientAddressNotFoundException;
use App\Domain\Client\ValueObject\ClientAddressId;
use App\Domain\Client\ValueObject\ClientId;
use App\Domain\Client\ValueObject\PhoneNumber;
use DateTimeImmutable;

/**
 * Агрегат клиента: профиль и адресная книга.
 */
final class Client
{
    /** @var list<object> */
    private array $recordedEvents = [];

    /** @param list<ClientAddress> $addresses */
    private function __construct(
        private ?ClientId $id,
        private string $name,
        private PhoneNumber $phone,
        private ?string $email,
        private ?DateTimeImmutable $birthDate,
        private string $passwordHash,
        private bool $consentPersonalData,
        private bool $consentMarketing,
        private array $addresses,
        private readonly DateTimeImmutable $createdAt,
    ) {}

    /**
     * @return list<ClientAddress>
     */
    public static function register(
        string $name,
        PhoneNumber $phone,
        ?string $email,
        ?DateTimeImmutable $birthDate,
        string $passwordHash,
        bool $consentPersonalData,
        bool $consentMarketing,
    ): self {
        $client = new self(
            id: null,
            name: $name,
            phone: $phone,
            email: $email,
            birthDate: $birthDate,
            passwordHash: $passwordHash,
            consentPersonalData: $consentPersonalData,
            consentMarketing: $consentMarketing,
            addresses: [],
            createdAt: new DateTimeImmutable(),
        );

        return $client;
    }

    public function markRegistered(): void
    {
        if ($this->id === null) {
            throw new \LogicException('Нельзя зафиксировать регистрацию без идентификатора клиента.');
        }

        $this->record(new ClientRegistered($this->id));
    }

    public function assignId(ClientId $id): void
    {
        $this->id = $id;
    }

    /**
     * @param list<ClientAddress> $addresses
     */
    public static function restore(
        ClientId $id,
        string $name,
        PhoneNumber $phone,
        ?string $email,
        ?DateTimeImmutable $birthDate,
        string $passwordHash,
        bool $consentPersonalData,
        bool $consentMarketing,
        array $addresses,
        DateTimeImmutable $createdAt,
    ): self {
        return new self(
            id: $id,
            name: $name,
            phone: $phone,
            email: $email,
            birthDate: $birthDate,
            passwordHash: $passwordHash,
            consentPersonalData: $consentPersonalData,
            consentMarketing: $consentMarketing,
            addresses: $addresses,
            createdAt: $createdAt,
        );
    }

    public function id(): ClientId
    {
        if ($this->id === null) {
            throw new \LogicException('Клиент ещё не сохранён.');
        }

        return $this->id;
    }

    public function hasId(): bool
    {
        return $this->id !== null;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function phone(): PhoneNumber
    {
        return $this->phone;
    }

    public function email(): ?string
    {
        return $this->email;
    }

    public function birthDate(): ?DateTimeImmutable
    {
        return $this->birthDate;
    }

    public function passwordHash(): string
    {
        return $this->passwordHash;
    }

    public function consentPersonalData(): bool
    {
        return $this->consentPersonalData;
    }

    public function consentMarketing(): bool
    {
        return $this->consentMarketing;
    }

    /**
     * @return list<ClientAddress>
     */
    public function addresses(): array
    {
        return $this->addresses;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updateProfile(
        string $name,
        PhoneNumber $phone,
        ?string $email,
        ?DateTimeImmutable $birthDate,
        ?bool $consentPersonalData = null,
        ?bool $consentMarketing = null,
    ): void {
        $this->name = $name;
        $this->phone = $phone;
        $this->email = $email;
        $this->birthDate = $birthDate;

        if ($consentPersonalData !== null) {
            $this->consentPersonalData = $consentPersonalData;
        }

        if ($consentMarketing !== null) {
            $this->consentMarketing = $consentMarketing;
        }
    }

    public function changePassword(string $passwordHash): void
    {
        $this->passwordHash = $passwordHash;
    }

    public function addAddress(ClientAddress $address): void
    {
        if ($address->isDefault() || $this->addresses === []) {
            $this->addresses = array_map(
                fn (ClientAddress $item): ClientAddress => $item->markNotDefault(),
                $this->addresses,
            );
            $address = $address->markDefault();
        }

        $this->addresses[] = $address;
    }

    public function assignAddressId(ClientAddress $address, ClientAddressId $id): void
    {
        $this->addresses = array_map(
            function (ClientAddress $item) use ($address, $id): ClientAddress {
                if ($item === $address) {
                    return $address->assignId($id);
                }

                return $item;
            },
            $this->addresses,
        );
    }

    public function removeAddress(ClientAddressId $addressId): void
    {
        $found = false;
        $nextAddresses = [];

        foreach ($this->addresses as $address) {
            if ($address->id()?->value() === $addressId->value()) {
                $found = true;

                continue;
            }

            $nextAddresses[] = $address;
        }

        if (! $found) {
            throw ClientAddressNotFoundException::forId($addressId->value());
        }

        if ($nextAddresses !== [] && ! $this->hasDefaultAddress($nextAddresses)) {
            $nextAddresses[0] = $nextAddresses[0]->markDefault();
        }

        $this->addresses = $nextAddresses;
    }

    public function defaultAddressId(): ?int
    {
        foreach ($this->addresses as $address) {
            if ($address->isDefault()) {
                return $address->id()?->value();
            }
        }

        return null;
    }

    /**
     * @return list<object>
     */
    public function releaseEvents(): array
    {
        $events = $this->recordedEvents;
        $this->recordedEvents = [];

        return $events;
    }

    private function record(object $event): void
    {
        $this->recordedEvents[] = $event;
    }

    /**
     * @param list<ClientAddress> $addresses
     */
    private function hasDefaultAddress(array $addresses): bool
    {
        foreach ($addresses as $address) {
            if ($address->isDefault()) {
                return true;
            }
        }

        return false;
    }
}
