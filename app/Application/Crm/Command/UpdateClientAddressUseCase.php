<?php

namespace App\Application\Crm\Command;

use App\Application\Crm\DTO\UpdateClientAddressDto;
use App\Application\Crm\Presenter\ClientPresenter;
use App\Domain\Supporting\Customer\Repository\ClientRepository;

final class UpdateClientAddressUseCase
{
    public function __construct(
        private readonly ClientRepository $clients,
        private readonly ClientPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(int $clientId, UpdateClientAddressDto $dto): array
    {
        $client = $this->clients->findById($clientId);
        if ($client === null) {
            throw new \InvalidArgumentException('Клиент не найден.');
        }

        $address = $client->updateAddress(
            addressId: $dto->addressId,
            type: $dto->type,
            title: $dto->title,
            street: $dto->street,
            house: $dto->house,
            entrance: $dto->entrance,
            floor: $dto->floor,
            apartment: $dto->apartment,
            comment: $dto->comment,
            lat: $dto->lat,
            lng: $dto->lng,
        );

        if ($dto->makeDefault === true) {
            $client->setDefaultAddress($dto->addressId);
        }

        $this->clients->save($client);

        return $this->presenter->address($address);
    }
}
