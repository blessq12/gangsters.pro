<?php

namespace App\Application\Client;

use App\Domain\Client\Repository\ClientRepository;
use Illuminate\Contracts\Hashing\Hasher;

abstract class ClientBaseUseCase
{
    public function __construct(
        protected readonly ClientRepository $clients,
        protected readonly Hasher $hasher,
    ) {
    }
}

