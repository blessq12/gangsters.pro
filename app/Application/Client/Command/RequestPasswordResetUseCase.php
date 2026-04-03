<?php

namespace App\Application\Client\Command;

use App\Application\Client\ClientBaseUseCase;
use App\Application\Client\DTO\RequestPasswordResetDTO;
use App\Application\Client\Ports\ClientPasswordResetMailer;
use App\Application\Client\Presenter\ClientPresenter;
use App\Domain\Client\Factory\ClientFactory;
use App\Domain\Client\Repository\ClientRepository;
use App\Shared\Auth\ClientAuthContext;
use App\Shared\Auth\ClientTokenService;
use App\Shared\Events\DomainEventBus;
use Illuminate\Contracts\Hashing\Hasher;

final class RequestPasswordResetUseCase extends ClientBaseUseCase
{
    public function __construct(
        ClientRepository $clients,
        ClientFactory $factory,
        Hasher $hasher,
        ClientAuthContext $authContext,
        ClientTokenService $tokens,
        ClientPresenter $presenter,
        DomainEventBus $events,
        private readonly ClientPasswordResetMailer $passwordResetMailer,
    ) {
        parent::__construct($clients, $factory, $hasher, $authContext, $tokens, $presenter, $events);
    }

    public function execute(RequestPasswordResetDTO $dto): void
    {
        $client = $this->clients->findByEmail($dto->email);

        if ($client === null) {
            return;
        }

        $token = bin2hex(random_bytes(16));

        $this->clients->setPasswordResetToken($dto->email, $token);
        $this->passwordResetMailer->sendResetLink($dto->email, $token);
    }
}
