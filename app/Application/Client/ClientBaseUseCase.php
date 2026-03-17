<?php

namespace App\Application\Client;

use App\Domain\Client\Repository\ClientRepository;
use App\Domain\Client\Factory\ClientFactory;
use App\Domain\Shared\Auth\ClientAuthContext;
use App\Domain\Shared\Auth\ClientTokenService;
use App\Application\Client\Presenter\ClientPresenter;
use Illuminate\Contracts\Hashing\Hasher;

abstract class ClientBaseUseCase
{
    public function __construct(
        protected readonly ClientRepository $clients,
        protected readonly ClientFactory $factory,
        protected readonly Hasher $hasher,
        protected readonly ClientAuthContext $authContext,
        protected readonly ClientTokenService $tokens,
        protected readonly ClientPresenter $presenter,
    ) {
    }
}

