<?php

namespace App\Application\YandexFood\Command;

use App\Application\YandexFood\DTO\IssueAccessTokenDto;
use App\Application\YandexFood\Port\YandexFoodAuthenticator;

final class IssueYandexFoodAccessTokenUseCase
{
    public function __construct(
        private readonly YandexFoodAuthenticator $authenticator,
    ) {}

    /**
     * @return array{access_token: string}
     */
    public function execute(IssueAccessTokenDto $input): array
    {
        $this->authenticator->authenticateClient($input->clientId, $input->clientSecret);

        return [
            'access_token' => $this->authenticator->accessToken(),
        ];
    }
}
