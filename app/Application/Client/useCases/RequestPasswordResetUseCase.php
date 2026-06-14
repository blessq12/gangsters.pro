<?php

namespace App\Application\Client\useCases;

use App\Application\Client\DTO\RequestPasswordResetDto;
use App\Domain\Client\Port\ClientPasswordResetNotifierPort;
use App\Domain\Client\Port\ClientPasswordResetTokenStorePort;
use App\Domain\Client\Repository\ClientRepository;
use Illuminate\Support\Str;

/**
 * Сценарий: запросить письмо со сбросом пароля.
 */
final class RequestPasswordResetUseCase
{
    public function __construct(
        private readonly ClientRepository $clients,
        private readonly ClientPasswordResetTokenStorePort $tokens,
        private readonly ClientPasswordResetNotifierPort $notifier,
    ) {}

    /**
     * @return array{message: string}
     */
    public function execute(RequestPasswordResetDto $input): array
    {
        $email = mb_strtolower(trim($input->email));
        $client = $this->clients->findByEmail($email);

        if ($client !== null) {
            $plainToken = Str::random(64);
            $this->tokens->store($email, $plainToken);
            $this->notifier->notify($email, $plainToken);
        }

        return [
            'message' => 'Если email зарегистрирован, мы отправили ссылку для сброса пароля.',
        ];
    }
}
