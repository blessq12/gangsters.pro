<?php

namespace App\Domain\Client\Entity;

use App\Domain\Client\VO\Email;
use App\Domain\Client\VO\PhoneNumber;
use DateTimeImmutable;

final class Client
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_BLOCKED = 'blocked';

    /**
     * @param ClientAddress[] $addresses
     */
    private function __construct(
        private ?int $id,
        private string $name,
        private PhoneNumber $phone,
        private ?Email $email,
        private ?DateTimeImmutable $birthDate,
        private ?string $passwordHash,
        private string $status,
        private bool $consentPersonalData,
        private bool $consentMarketing,
        private ?int $defaultAddressId,
        private array $addresses,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
        private ?DateTimeImmutable $deletedAt,
    ) {
    }

    public static function register(
        string $name,
        PhoneNumber $phone,
        ?Email $email,
        ?DateTimeImmutable $birthDate,
        ?string $passwordHash,
        bool $consentPersonalData,
        bool $consentMarketing,
    ): self {
        $now = new DateTimeImmutable();

        return new self(
            id: null,
            name: $name,
            phone: $phone,
            email: $email,
            birthDate: $birthDate,
            passwordHash: $passwordHash,
            status: self::STATUS_ACTIVE,
            consentPersonalData: $consentPersonalData,
            consentMarketing: $consentMarketing,
            defaultAddressId: null,
            addresses: [],
            createdAt: $now,
            updatedAt: $now,
            deletedAt: null,
        );
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function phone(): PhoneNumber
    {
        return $this->phone;
    }

    public function email(): ?Email
    {
        return $this->email;
    }

    public function birthDate(): ?DateTimeImmutable
    {
        return $this->birthDate;
    }

    public function passwordHash(): ?string
    {
        return $this->passwordHash;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE && $this->deletedAt === null;
    }

    public function consentPersonalData(): bool
    {
        return $this->consentPersonalData;
    }

    public function consentMarketing(): bool
    {
        return $this->consentMarketing;
    }

    public function defaultAddressId(): ?int
    {
        return $this->defaultAddressId;
    }

    /**
     * @return ClientAddress[]
     */
    public function addresses(): array
    {
        return $this->addresses;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function deletedAt(): ?DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function rename(string $name): void
    {
        $this->name = $name;
        $this->touch();
    }

    public function changeContacts(PhoneNumber $phone, ?Email $email): void
    {
        $this->phone = $phone;
        $this->email = $email;
        $this->touch();
    }

    public function changeBirthDate(?DateTimeImmutable $birthDate): void
    {
        $this->birthDate = $birthDate;
        $this->touch();
    }

    public function changePasswordHash(?string $passwordHash): void
    {
        $this->passwordHash = $passwordHash;
        $this->touch();
    }

    public function block(): void
    {
        $this->status = self::STATUS_BLOCKED;
        $this->touch();
    }

    public function activate(): void
    {
        $this->status = self::STATUS_ACTIVE;
        $this->touch();
    }

    public function updateConsents(bool $personalData, bool $marketing): void
    {
        $this->consentPersonalData = $personalData;
        $this->consentMarketing = $marketing;
        $this->touch();
    }

    public function setDefaultAddress(ClientAddress $address): void
    {
        $this->defaultAddressId = $address->id();
        $this->touch();
    }

    public function addAddress(ClientAddress $address): void
    {
        $this->addresses[] = $address;
        $this->touch();
    }

    public function replaceAddresses(array $addresses): void
    {
        $this->addresses = $addresses;
        $this->touch();
    }

    public function softDelete(): void
    {
        $this->deletedAt = new DateTimeImmutable();
        $this->touch();
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}

