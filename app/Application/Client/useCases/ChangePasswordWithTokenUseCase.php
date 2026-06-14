<?php

namespace App\Application\Client\useCases;

use App\Application\Client\DTO\ChangePasswordWithTokenDto;
use App\Domain\Client\Exception\ClientNotFoundException;
use App\Domain\Client\Exception\InvalidPasswordResetTokenException;
use App\Domain\Client\Port\ClientPasswordResetTokenStorePort;
use App\Domain\Client\Repository\ClientRepository;
use Illuminate\Support\Facades\Hash;

/**
 * Сценарий: сменить пароль по токену из письма.
 */
final class ChangePasswordWithTokenUseCase
{
    public function __construct(
        private readonly ClientRepository $clients,
        private readonly ClientPasswordResetTokenStorePort $tokens,
    ) {}

    /**
     * @return array{message: string}
     */
    public function execute(ChangePasswordWithTokenDto $input): array
    {
        $email = $this->tokens->resolveEmailByToken($input->token);

        if ($email === null) {
            throw InvalidPasswordResetTokenException::expiredOrInvalid();
        }

        $client = $this->clients->findByEmail($email);

        if ($client === null) {
            throw ClientNotFoundException::byEmail($email);
        }

        $client->changePassword(Hash::make($input->password));
        $this->clients->save($client);
        $this->tokens->delete($email);

        return [
            'message' => 'Пароль обновлён.',
        ];
    }
}
