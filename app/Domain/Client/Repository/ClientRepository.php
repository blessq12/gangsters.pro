<?php

namespace App\Domain\Client\Repository;

use App\Domain\Client\Entity\Client;
use App\Domain\Client\Entity\ClientAddress;

interface ClientRepository
{
    public function findById(int $id): ?Client;

    public function findByPhone(string $phone): ?Client;

    public function findByEmail(string $email): ?Client;

    public function existsByPhone(string $phone): bool;

    public function existsByEmail(string $email): bool;

    public function save(Client $client): void;

    public function delete(Client $client): void;

    public function addAddress(int $clientId, ClientAddress $address, bool $makeDefault): Client;

    public function deleteAddress(int $clientId, int $addressId): Client;

    public function setPasswordResetToken(string $email, string $token): void;

    public function findByPasswordResetToken(string $token): ?Client;

    public function clearPasswordResetToken(Client $client): void;
}

