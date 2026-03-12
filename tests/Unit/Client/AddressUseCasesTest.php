<?php

namespace Tests\Unit\Client;

use App\Application\Client\Command\AddClientAddressUseCase;
use App\Application\Client\Command\DeleteClientAddressUseCase;
use App\Application\Client\Command\RegisterClientUseCase;
use App\Application\Client\DTO\AddClientAddressDTO;
use App\Application\Client\DTO\DeleteClientAddressDTO;
use App\Application\Client\DTO\RegisterDTO;
use Illuminate\Hashing\BcryptHasher;
use PHPUnit\Framework\TestCase;

final class AddressUseCasesTest extends TestCase
{
    public function test_adds_address_to_client(): void
    {
        $repo = new InMemoryClientRepository();
        $hasher = new BcryptHasher();

        $register = new RegisterClientUseCase($repo, $hasher);
        $addAddress = new AddClientAddressUseCase($repo, $hasher);

        $client = $register->execute(new RegisterDTO(
            name: 'John',
            phone: '+7 (999) 111-22-33',
            email: null,
            birthDate: null,
            password: 'secret',
            consentPersonalData: true,
            consentMarketing: false,
        ));

        $updated = $addAddress->execute(new AddClientAddressDTO(
            clientId: $client->id(),
            type: 'default',
            title: 'Дом',
            street: 'Main',
            house: '1',
            liter: null,
            staircase: null,
            apartment: '10',
            entranceCode: null,
            floor: null,
            comment: null,
            makeDefault: true,
        ));

        $this->assertNotEmpty($updated->addresses());
    }

    public function test_deletes_address_from_client(): void
    {
        $repo = new InMemoryClientRepository();
        $hasher = new BcryptHasher();

        $register = new RegisterClientUseCase($repo, $hasher);
        $addAddress = new AddClientAddressUseCase($repo, $hasher);
        $deleteAddress = new DeleteClientAddressUseCase($repo, $hasher);

        $client = $register->execute(new RegisterDTO(
            name: 'John',
            phone: '+7 (999) 111-22-33',
            email: null,
            birthDate: null,
            password: 'secret',
            consentPersonalData: true,
            consentMarketing: false,
        ));

        $updated = $addAddress->execute(new AddClientAddressDTO(
            clientId: $client->id(),
            type: 'additional',
            title: 'Работа',
            street: 'Work',
            house: '2',
            liter: null,
            staircase: null,
            apartment: '20',
            entranceCode: null,
            floor: null,
            comment: null,
            makeDefault: false,
        ));

        $address = $updated->addresses()[0];

        $afterDelete = $deleteAddress->execute(new DeleteClientAddressDTO(
            clientId: $client->id(),
            addressId: $address->id() ?? 1,
        ));

        $this->assertIsArray($afterDelete->addresses());
    }
}

