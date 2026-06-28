<?php

namespace App\Application\YandexFood\DTO;

final readonly class IssueAccessTokenDto
{
    public function __construct(
        public ?string $clientId,
        public ?string $clientSecret,
    ) {}
}
