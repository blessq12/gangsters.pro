<?php

namespace App\Domain\Supporting\Customer\Repository;

use App\Domain\Supporting\Customer\Entity\Client;

interface ClientRepository
{
    public function save(Client $client): void;

    public function findById(int $id): ?Client;

    public function findByPhone(string $phone): ?Client;

    public function findByEmail(string $email): ?Client;

    public function existsByPhone(string $phone): bool;

    public function count(): int;

    /**
     * @return list<Client>
     */
    public function listPage(int $offset, int $limit): array;
}
