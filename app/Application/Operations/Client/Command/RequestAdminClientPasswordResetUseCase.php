<?php

namespace App\Application\Operations\Client\Command;

use App\Application\Client\Command\RequestPasswordResetUseCase;
use App\Application\Client\DTO\RequestPasswordResetDTO;
use App\Application\Common\Exceptions\ApiException;
use App\Domain\Client\Repository\ClientRepository;

final class RequestAdminClientPasswordResetUseCase
{
    public function __construct(
        private readonly ClientRepository $clients,
        private readonly RequestPasswordResetUseCase $requestPasswordReset,
    ) {
    }

    public function execute(int $clientId): void
    {
        $client = $this->clients->findById($clientId);
        if ($client === null) {
            throw new ApiException('Client not found.', 404);
        }

        if ($client->email() === null) {
            throw new ApiException('У клиента не указан email для сброса пароля.', 422);
        }

        $this->requestPasswordReset->execute(new RequestPasswordResetDTO((string) $client->email()));
    }
}
