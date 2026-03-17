<?php

namespace App\Application\Client\Command;

use App\Application\Client\ClientBaseUseCase;
use App\Application\Client\DTO\DeleteClientAddressDTO;
use App\Application\Client\Presenter\ClientPresenter;

final class DeleteClientAddressUseCase extends ClientBaseUseCase
{
    public function __construct(
        ClientRepository $clients,
        Hasher $hasher,
        ClientAuthContext $authContext,
        ClientTokenService $tokens,
        private readonly ClientPresenter $presenter,
    ) {
        parent::__construct($clients, $hasher, $authContext, $tokens);
    }

    public function execute(DeleteClientAddressDTO $dto): array
    {
        $clientId = $this->authContext->currentClientId();

        $client = $this->clients->deleteAddress($clientId, $dto->addressId);

        return [
            'client' => $this->presenter->present($client),
        ];
    }
}

