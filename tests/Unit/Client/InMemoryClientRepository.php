<?php

namespace Tests\Unit\Client;

use App\Domain\Client\Entity\Client;
use App\Domain\Client\Entity\ClientAddress;
use App\Domain\Client\Repository\ClientRepository;

final class InMemoryClientRepository implements ClientRepository
{
    /** @var array<int, Client> */
    private array $byId = [];

    /** @var array<string, int> */
    private array $idByPhone = [];

    /** @var array<string, int> */
    private array $idByEmail = [];

    /** @var array<int, ClientAddress[]> */
    private array $addressesByClientId = [];

    /** @var array<string, string> */
    private array $resetTokenByEmail = [];

    /** @var array<string, string> */
    private array $emailByResetToken = [];

    private int $autoIncrement = 1;

    public function findById(int $id): ?Client
    {
        return $this->byId[$id] ?? null;
    }

    public function findByPhone(string $phone): ?Client
    {
        $normalized = $this->normalizePhone($phone);
        $id = $this->idByPhone[$normalized] ?? null;

        return $id !== null ? $this->findById($id) : null;
    }

    public function findByEmail(string $email): ?Client
    {
        $id = $this->idByEmail[$email] ?? null;

        return $id !== null ? $this->findById($id) : null;
    }

    public function existsByPhone(string $phone): bool
    {
        $normalized = $this->normalizePhone($phone);

        return isset($this->idByPhone[$normalized]);
    }

    public function existsByEmail(string $email): bool
    {
        return isset($this->idByEmail[$email]);
    }

    public function save(Client $client): void
    {
        $id = $client->id() ?? $this->autoIncrement++;

        $ref = new \ReflectionClass($client);
        $prop = $ref->getProperty('id');
        $prop->setAccessible(true);
        $prop->setValue($client, $id);

        $this->byId[$id] = $client;

        $phone = (string) $client->phone();
        $this->idByPhone[$this->normalizePhone($phone)] = $id;

        if ($client->email() !== null) {
            $email = (string) $client->email();
            $this->idByEmail[$email] = $id;
        }
    }

    private function normalizePhone(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone) ?? $phone;
    }

    public function delete(Client $client): void
    {
        $id = $client->id();

        if ($id === null) {
            return;
        }

        unset($this->byId[$id]);
        unset($this->addressesByClientId[$id]);
    }

    public function addAddress(int $clientId, ClientAddress $address, bool $makeDefault): Client
    {
        $client = $this->findById($clientId);

        if ($client === null) {
            return $this->createDummyClient();
        }

        $this->addressesByClientId[$clientId][] = $address;

        $ref = new \ReflectionClass($client);

        $addressesProp = $ref->getProperty('addresses');
        $addressesProp->setAccessible(true);
        $addressesProp->setValue($client, $this->addressesByClientId[$clientId]);

        if ($makeDefault) {
            $defaultProp = $ref->getProperty('defaultAddressId');
            $defaultProp->setAccessible(true);
            $defaultProp->setValue($client, $address->id() ?? 1);
        }

        return $client;
    }

    public function deleteAddress(int $clientId, int $addressId): Client
    {
        $client = $this->findById($clientId);

        if ($client === null) {
            return $this->createDummyClient();
        }

        $this->addressesByClientId[$clientId] = array_values(array_filter(
            $this->addressesByClientId[$clientId] ?? [],
            static fn (ClientAddress $address) => $address->id() !== $addressId,
        ));

        $ref = new \ReflectionClass($client);

        $addressesProp = $ref->getProperty('addresses');
        $addressesProp->setAccessible(true);
        $addressesProp->setValue($client, $this->addressesByClientId[$clientId]);

        return $client;
    }

    public function setPasswordResetToken(string $email, string $token): void
    {
        $this->resetTokenByEmail[$email] = $token;
        $this->emailByResetToken[$token] = $email;
    }

    public function findByPasswordResetToken(string $token): ?Client
    {
        $email = $this->emailByResetToken[$token] ?? null;

        if ($email === null) {
            return null;
        }

        return $this->findByEmail($email);
    }

    public function clearPasswordResetToken(Client $client): void
    {
        $emailVo = $client->email();

        if ($emailVo === null) {
            return;
        }

        $email = (string) $emailVo;

        $token = $this->resetTokenByEmail[$email] ?? null;

        if ($token !== null) {
            unset($this->emailByResetToken[$token]);
        }

        unset($this->resetTokenByEmail[$email]);
    }

    private function createDummyClient(): Client
    {
        $ref = new \ReflectionClass(Client::class);

        /** @var Client $client */
        $client = $ref->newInstanceWithoutConstructor();

        return $client;
    }
}

