<?php

namespace App\Infrastructure\Order\CustomerSnapshot;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Order\Contracts\CustomerSnapshotProvider;
use App\Domain\Client\Repository\ClientRepository;
use App\Domain\Order\Factories\CustomerSnapshotFactory;
use App\Domain\Order\ValueObjects\CustomerSnapshot;

final class EloquentCustomerSnapshotProvider implements CustomerSnapshotProvider
{
    public function __construct(
        private readonly ClientRepository $clients,
        private readonly CustomerSnapshotFactory $factory,
    ) {
    }

    public function forAuthenticatedClient(int $clientId): CustomerSnapshot
    {
        $client = $this->clients->findById($clientId);
        if ($client === null) {
            throw new ApiException('Client not found.');
        }
        if (! $client->isActive()) {
            throw new ApiException('Client is blocked or deleted.');
        }

        return $this->factory->fromAuthenticatedClientData(
            name: $client->name(),
            phone: (string) $client->phone(),
            email: $client->email() !== null ? (string) $client->email() : null,
            addresses: array_map(
                static fn ($address): array => [
                    'id' => $address->id(),
                    'street' => $address->street(),
                    'house' => $address->house(),
                    'entrance' => $address->entrance(),
                    'apartment' => $address->apartment(),
                ],
                $client->addresses(),
            ),
            defaultAddressId: $client->defaultAddressId(),
        );
    }

    public function forGuestContact(string $name, string $phone, ?string $email): CustomerSnapshot
    {
        return $this->factory->fromGuestContact($name, $phone, $email);
    }

    public function forExternalContact(string $name, string $phone): CustomerSnapshot
    {
        return new CustomerSnapshot(
            name: $name,
            phone: $phone,
            email: null,
            address: null,
        );
    }
}
