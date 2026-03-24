<?php

namespace App\Application\Client\Command;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Client\ClientBaseUseCase;
use App\Application\Client\DTO\ChangePasswordDTO;
use App\Domain\Client\Events\ClientPasswordChanged;
use DateInterval;
use DateTimeImmutable;

final class ChangePasswordUseCase extends ClientBaseUseCase
{
    public function execute(ChangePasswordDTO $dto): array
    {
        $tokenTtlMinutes = (int) config('auth.password_reset_token_ttl_minutes', 30);
        $requestedAfter = (new DateTimeImmutable())->sub(new DateInterval('PT'.$tokenTtlMinutes.'M'));
        $client = $this->clients->findByPasswordResetTokenRequestedAfter($dto->token, $requestedAfter);

        if ($client === null) {
            throw new ApiException('Invalid token');
        }

        $client->changePasswordHash($this->hasher->make($dto->password));

        $this->clients->save($client);
        $this->clients->clearPasswordResetToken($client);
        $this->events->publish(new ClientPasswordChanged($client));

        return [
            'client' => $this->presenter->present($client),
        ];
    }
}

