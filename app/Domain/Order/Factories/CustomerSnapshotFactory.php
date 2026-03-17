<?php

namespace App\Domain\Order\Factories;

use App\Domain\Client\Entity\Client;
use App\Domain\Client\Entity\ClientAddress;
use App\Domain\Order\ValueObjects\CustomerSnapshot;

final class CustomerSnapshotFactory
{
    public function fromClient(Client $client): CustomerSnapshot
    {
        $address = null;
        $addresses = $client->addresses();

        if (\count($addresses) > 0) {
            $addr = $client->defaultAddressId() !== null
                ? $this->findAddressById($addresses, $client->defaultAddressId())
                : $addresses[0];

            if ($addr !== null) {
                $address = [
                    'street' => $addr->street(),
                    'house' => $addr->house(),
                    'entrance' => $addr->entrance(),
                    'apartment' => $addr->apartment(),
                ];
            }
        }

        return new CustomerSnapshot(
            name: $client->name(),
            phone: (string) $client->phone(),
            email: $client->email() !== null ? (string) $client->email() : null,
            address: $address,
        );
    }

    public function forGuest(): CustomerSnapshot
    {
        return new CustomerSnapshot(
            name: 'Гость',
            phone: '',
            email: null,
            address: null,
        );
    }

    /**
     * @param ClientAddress[] $addresses
     */
    private function findAddressById(array $addresses, int $id): ?ClientAddress
    {
        foreach ($addresses as $a) {
            if ($a->id() === $id) {
                return $a;
            }
        }

        return null;
    }
}

