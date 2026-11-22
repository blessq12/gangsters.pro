<?php

namespace App\Infrastructure\Order\Adapter;

use App\Domain\Core\Order\Port\OrderClientLookupPort;
use App\Domain\Supporting\Customer\Repository\ClientRepository;

/**
 * ACL: Order ищет клиента через ClientRepository (CRM/Customer) в Infrastructure.
 */
final class EloquentOrderClientLookupPort implements OrderClientLookupPort
{
    public function __construct(
        private readonly ClientRepository $clients,
    ) {}

    public function findById(int $id): ?array
    {
        $client = $this->clients->findById($id);
        if ($client === null) {
            return null;
        }

        return [
            'id' => $client->id(),
            'name' => $client->name(),
            'phone' => $client->phone(),
            'email' => $client->email(),
        ];
    }
}
