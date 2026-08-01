<?php

namespace App\Application\Crm\Command;

use App\Application\Crm\Presenter\ClientPresenter;
use App\Domain\Crm\Entity\ClientAddress;
use App\Domain\Crm\Repository\ClientRepository;
use Illuminate\Auth\AuthenticationException;

final class AddClientAddressUseCase
{
    public function __construct(
        private readonly ClientRepository $clients,
        private readonly ClientPresenter $presenter,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     * @return array{client: array<string, mixed>}
     */
    public function execute(int $clientId, array $input): array
    {
        $client = $this->clients->findById($clientId);
        if ($client === null) {
            throw new AuthenticationException();
        }

        $makeDefault = (bool) ($input['make_default'] ?? $client->addresses() === []);

        $address = ClientAddress::create(
            type: isset($input['type']) ? (string) $input['type'] : null,
            title: isset($input['title']) ? (string) $input['title'] : null,
            street: (string) ($input['street'] ?? ''),
            house: (string) ($input['house'] ?? ''),
            entrance: isset($input['entrance']) ? (string) $input['entrance'] : null,
            apartment: isset($input['apartment']) ? (string) $input['apartment'] : null,
            comment: isset($input['comment']) ? (string) $input['comment'] : null,
            makeDefault: $makeDefault,
        );

        $client->addAddress($address);
        $this->clients->save($client);

        return ['client' => $this->presenter->present($client)];
    }
}
