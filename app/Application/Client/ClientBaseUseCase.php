<?php

namespace App\Application\Client;

use App\Domain\Client\Repository\ClientRepository;
use App\Domain\Shared\Auth\ClientAuthContext;
use App\Domain\Shared\Auth\ClientTokenService;
use Illuminate\Contracts\Hashing\Hasher;

abstract class ClientBaseUseCase
{
    public function __construct(
        protected readonly ClientRepository $clients,
        protected readonly Hasher $hasher,
        protected readonly ClientAuthContext $authContext,
        protected readonly ClientTokenService $tokens,
    ) {
    }
}

