<?php

namespace App\Domain\Crm\Repository;

use App\Domain\Crm\Entity\Client;

interface ClientRepository
{
    public function save(Client $client): void;

    public function findById(int $id): ?Client;

    public function findByPhone(string $phone): ?Client;

    public function findByEmail(string $email): ?Client;

    public function existsByPhone(string $phone): bool;
}
