<?php

namespace Tests\Unit\Client;

use App\Application\Client\Command\RegisterClientUseCase;
use App\Application\Client\DTO\RegisterDTO;
use App\Application\Client\Query\GetClientDataUseCase;
use Illuminate\Hashing\BcryptHasher;
use LogicException;
use PHPUnit\Framework\TestCase;

final class GetClientDataUseCaseTest extends TestCase
{
    public function test_returns_client_by_id(): void
    {
        $repo = new InMemoryClientRepository();
        $hasher = new BcryptHasher();

        $register = new RegisterClientUseCase($repo, $hasher);
        $getClient = new GetClientDataUseCase($repo, $hasher);

        $client = $register->execute(new RegisterDTO(
            name: 'John Doe',
            phone: '+79998887766',
            email: null,
            birthDate: null,
            password: 'secret',
            consentPersonalData: true,
            consentMarketing: false,
        ));

        $loaded = $getClient->execute($client->id());

        $this->assertSame($client->id(), $loaded->id());
    }

    public function test_throws_when_not_found(): void
    {
        $this->expectException(LogicException::class);

        $repo = new InMemoryClientRepository();
        $hasher = new BcryptHasher();

        $getClient = new GetClientDataUseCase($repo, $hasher);

        $getClient->execute(999);
    }
}

