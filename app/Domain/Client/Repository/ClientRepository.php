<?php

namespace App\Domain\Client\Repository;

use App\Domain\Client\Entity\Client;
use App\Domain\Client\ValueObject\ClientId;
use App\Domain\Client\ValueObject\PhoneNumber;

interface ClientRepository
{
    public function findById(ClientId $id): ?Client;

    public function findByPhone(PhoneNumber $phone): ?Client;

    public function findByEmail(string $email): ?Client;

    public function existsByPhone(PhoneNumber $phone): bool;

    public function existsByEmail(string $email): bool;

    public function save(Client $client): void;
}
