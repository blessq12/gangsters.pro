<?php

namespace App\Application\Crm\Command;

use App\Application\Crm\DTO\AddClientAddressDto;
use App\Application\Crm\Presenter\ClientPresenter;
use App\Domain\Supporting\Customer\Entity\ClientAddress;
use App\Domain\Supporting\Customer\Repository\ClientRepository;

final class AddClientAddressUseCase
{
    public function __construct(
        private readonly ClientRepository $clients,
        private readonly ClientPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(int $clientId, AddClientAddressDto $dto): array
    {
        $client = $this->clients->findById($clientId);
        if ($client === null) {
            throw new \InvalidArgumentException('Клиент не найден.');
        }

        $address = ClientAddress::create(
            type: $dto->type,
            title: $dto->title,
            street: $dto->street,
            house: $dto->house,
            entrance: $dto->entrance,
            floor: $dto->floor,
            apartment: $dto->apartment,
            comment: $dto->comment,
            makeDefault: $dto->makeDefault || $client->addresses() === [],
        );

        $client->addAddress($address);
        $this->clients->save($client);

        return $this->presenter->address($address);
    }
}
