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

    public function fromGuestContact(string $name, string $phone, ?string $email): CustomerSnapshot
    {
        return new CustomerSnapshot(
            name: trim($name),
            phone: $this->normalizePhoneForStorage($phone),
            email: $email !== null && trim($email) !== '' ? trim($email) : null,
            address: null,
        );
    }

    private function normalizePhoneForStorage(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '') {
            return trim($phone);
        }

        if (strlen($digits) === 11 && in_array($digits[0], ['7', '8'], true)) {
            $digits = substr($digits, 1);
        }

        if (strlen($digits) !== 10) {
            return trim($phone);
        }

        $code = substr($digits, 0, 3);
        $part1 = substr($digits, 3, 3);
        $part2 = substr($digits, 6, 2);
        $part3 = substr($digits, 8, 2);

        return sprintf('+7 (%s) %s-%s-%s', $code, $part1, $part2, $part3);
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

