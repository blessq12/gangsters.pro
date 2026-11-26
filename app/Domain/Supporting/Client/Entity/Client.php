<?php

namespace App\Domain\Supporting\Client\Entity;

use DateTimeImmutable;

/** Агрегат клиента: профиль + адреса + избранные productId. */
final class Client
{
    /**
     * @param  list<ClientAddress>  $addresses
     * @param  list<int>  $favoriteProductIds
     */
    private function __construct(
        private ?int $id,
        private string $name,
        private string $phone,
        private ?string $email,
        private ?DateTimeImmutable $birthDate,
        private string $passwordHash,
        private bool $consentPersonalData,
        private bool $consentMarketing,
        private array $addresses,
        private array $favoriteProductIds,
        private readonly DateTimeImmutable $createdAt,
    ) {}

    public static function register(
        string $name,
        string $phone,
        ?string $email,
        ?DateTimeImmutable $birthDate,
        string $passwordHash,
        bool $consentPersonalData,
        bool $consentMarketing,
    ): self {
        $name = trim($name);
        $phone = trim($phone);

        if ($name === '' || $phone === '') {
            throw new \InvalidArgumentException('Имя и телефон обязательны.');
        }

        if ($passwordHash === '') {
            throw new \InvalidArgumentException('Хеш пароля обязателен.');
        }

        if (! $consentPersonalData) {
            throw new \InvalidArgumentException('Согласие на ПДн обязательно.');
        }

        return new self(
            id: null,
            name: $name,
            phone: $phone,
            email: self::normalizeOptional($email),
            birthDate: $birthDate,
            passwordHash: $passwordHash,
            consentPersonalData: true,
            consentMarketing: $consentMarketing,
            addresses: [],
            favoriteProductIds: [],
            createdAt: new DateTimeImmutable,
        );
    }

    /**
     * Клиент из бэкофиса (POS): без пароля для входа в ЛК, ПДн отмечены оператором.
     */
    public static function registerByStaff(
        string $name,
        string $phone,
        string $passwordHash,
    ): self {
        return self::register(
            name: $name,
            phone: $phone,
            email: null,
            birthDate: null,
            passwordHash: $passwordHash,
            consentPersonalData: true,
            consentMarketing: false,
        );
    }

    /**
     * @param  list<ClientAddress>  $addresses
     * @param  list<int>  $favoriteProductIds
     */
    public static function restore(
        int $id,
        string $name,
        string $phone,
        ?string $email,
        ?DateTimeImmutable $birthDate,
        string $passwordHash,
        bool $consentPersonalData,
        bool $consentMarketing,
        array $addresses,
        array $favoriteProductIds,
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
            favoriteProductIds: array_values($favoriteProductIds),
            createdAt: $createdAt,
        );
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

    public function id(): int
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

    public function phone(): string
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

    /**
     * @return list<int>
     */
    public function favoriteProductIds(): array
    {
        return $this->favoriteProductIds;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updateProfile(
        string $name,
        string $phone,
        ?string $email,
        ?DateTimeImmutable $birthDate,
        ?bool $consentMarketing = null,
    ): void {
        $name = trim($name);
        $phone = trim($phone);

        if ($name === '' || $phone === '') {
            throw new \InvalidArgumentException('Имя и телефон обязательны.');
        }

        $this->name = $name;
        $this->phone = $phone;
        $this->email = self::normalizeOptional($email);
        $this->birthDate = $birthDate;

        if ($consentMarketing !== null) {
            $this->consentMarketing = $consentMarketing;
        }
    }

    public function changePassword(string $passwordHash): void
    {
        if ($passwordHash === '') {
            throw new \InvalidArgumentException('Хеш пароля обязателен.');
        }

        $this->passwordHash = $passwordHash;
    }

    public function addAddress(ClientAddress $address): void
    {
        if ($address->isDefault()) {
            $this->clearDefaultAddresses();
        }

        $this->addresses[] = $address;
    }

    public function removeAddress(string $addressId): void
    {
        $this->addresses = array_values(array_filter(
            $this->addresses,
            static fn (ClientAddress $address): bool => $address->id() !== $addressId,
        ));
    }

    public function setDefaultAddress(string $addressId): void
    {
        $found = false;

        foreach ($this->addresses as $address) {
            if ($address->id() === $addressId) {
                $address->markDefault();
                $found = true;
            } else {
                $address->markNotDefault();
            }
        }

        if (! $found) {
            throw new \InvalidArgumentException('Адрес не найден.');
        }
    }

    public function updateAddress(
        string $addressId,
        ?string $type,
        ?string $title,
        string $street,
        string $house,
        ?string $entrance,
        ?string $floor,
        ?string $apartment,
        ?string $comment,
        ?float $lat = null,
        ?float $lng = null,
    ): ClientAddress {
        foreach ($this->addresses as $address) {
            if ($address->id() !== $addressId) {
                continue;
            }

            $address->update(
                type: $type,
                title: $title,
                street: $street,
                house: $house,
                entrance: $entrance,
                floor: $floor,
                apartment: $apartment,
                comment: $comment,
                lat: $lat,
                lng: $lng,
            );

            return $address;
        }

        throw new \InvalidArgumentException('Адрес не найден.');
    }

    public function addFavoriteProductId(int $productId): void
    {
        if ($productId < 1) {
            throw new \InvalidArgumentException('productId должен быть положительным.');
        }

        if (in_array($productId, $this->favoriteProductIds, true)) {
            return;
        }

        $this->favoriteProductIds[] = $productId;
    }

    public function removeFavoriteProductId(int $productId): void
    {
        $this->favoriteProductIds = array_values(array_filter(
            $this->favoriteProductIds,
            static fn (int $id): bool => $id !== $productId,
        ));
    }

    public function toggleFavoriteProductId(int $productId): void
    {
        if (in_array($productId, $this->favoriteProductIds, true)) {
            $this->removeFavoriteProductId($productId);

            return;
        }

        $this->addFavoriteProductId($productId);
    }

    /**
     * @param  list<int>  $productIds
     */
    public function mergeFavoriteProductIds(array $productIds): void
    {
        foreach ($productIds as $productId) {
            $this->addFavoriteProductId((int) $productId);
        }
    }

    private function clearDefaultAddresses(): void
    {
        foreach ($this->addresses as $address) {
            $address->markNotDefault();
        }
    }

    private static function normalizeOptional(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
