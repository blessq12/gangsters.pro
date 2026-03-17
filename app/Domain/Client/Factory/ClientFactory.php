<?php

namespace App\Domain\Client\Factory;

use App\Domain\Client\Entity\Client;
use App\Domain\Client\VO\Email;
use App\Domain\Client\VO\PhoneNumber;
use DateTimeImmutable;
use Illuminate\Contracts\Hashing\Hasher;

final class ClientFactory
{
    public function __construct(
        private readonly Hasher $hasher,
    ) {
    }

    public function createNew(
        string $name,
        string $phone,
        ?string $email,
        ?string $birthDate,
        ?string $rawPassword,
        bool $consentPersonalData,
        bool $consentMarketing,
    ): Client {
        $birth = $birthDate !== null
            ? new DateTimeImmutable($birthDate)
            : null;

        $emailVo = $email !== null ? new Email($email) : null;
        $phoneVo = new PhoneNumber($phone);
        $passwordHash = $rawPassword !== null
            ? $this->hasher->make($rawPassword)
            : null;

        return Client::register(
            name: $name,
            phone: $phoneVo,
            email: $emailVo,
            birthDate: $birth,
            passwordHash: $passwordHash,
            consentPersonalData: $consentPersonalData,
            consentMarketing: $consentMarketing,
        );
    }

    public function changeContactsFromPrimitives(
        Client $client,
        ?string $phone,
        ?string $email,
    ): void {
        $newPhone = $phone !== null ? new PhoneNumber($phone) : $client->phone();
        $newEmail = $email !== null ? new Email($email) : $client->email();

        $client->changeContacts($newPhone, $newEmail);
    }
}

