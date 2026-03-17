<?php

namespace App\Application\Client\Command;

use App\Application\Client\ClientBaseUseCase;
use App\Application\Client\DTO\RegisterDTO;
use App\Application\Client\Presenter\ClientPresenter;
use App\Domain\Client\VO\Email;
use App\Domain\Client\VO\PhoneNumber;
use DateTimeImmutable;
use LogicException;

final class RegisterClientUseCase extends ClientBaseUseCase
{
    public function __construct(
        ClientRepository $clients,
        Hasher $hasher,
        ClientAuthContext $authContext,
        ClientTokenService $tokens,
        private readonly ClientPresenter $presenter,
    ) {
        parent::__construct($clients, $hasher, $authContext, $tokens);
    }
    public function execute(RegisterDTO $dto): array
    {
        if ($this->clients->existsByPhone($dto->phone)) {
            throw new LogicException('Client with this phone already exists');
        }

        if ($dto->email !== null && $this->clients->existsByEmail($dto->email)) {
            throw new LogicException('Client with this email already exists');
        }

        $birthDate = $dto->birthDate !== null
            ? new DateTimeImmutable($dto->birthDate)
            : null;

        $passwordHash = $dto->password !== null
            ? $this->hasher->make($dto->password)
            : null;

        $client = Client::register(
            name: $dto->name,
            phone: new PhoneNumber($dto->phone),
            email: $dto->email !== null ? new Email($dto->email) : null,
            birthDate: $birthDate,
            passwordHash: $passwordHash,
            consentPersonalData: $dto->consentPersonalData,
            consentMarketing: $dto->consentMarketing,
        );

        $this->clients->save($client);

        $token = $this->tokens->issueTokenForClient($client->id());

        return [
            'client' => $this->presenter->present($client),
            'token' => $token,
        ];
    }
}

