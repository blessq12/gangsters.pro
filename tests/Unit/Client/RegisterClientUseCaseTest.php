<?php

namespace Tests\Unit\Client;

use App\Application\Client\ClientBaseUseCase;
use App\Application\Client\Command\RegisterClientUseCase;
use App\Application\Client\DTO\RegisterDTO;
use Illuminate\Hashing\BcryptHasher;
use LogicException;
use PHPUnit\Framework\TestCase;

final class RegisterClientUseCaseTest extends TestCase
{
    public function test_registers_new_client(): void
    {
        $repo = new InMemoryClientRepository();
        $hasher = new BcryptHasher();

        $useCase = new RegisterClientUseCase($repo, $hasher);

        $dto = new RegisterDTO(
            name: 'John Doe',
            phone: '+79998887766',
            email: 'john@example.com',
            birthDate: '1990-01-01',
            password: 'secret',
            consentPersonalData: true,
            consentMarketing: false,
        );

        $client = $useCase->execute($dto);

        $this->assertNotNull($client->id());
        $this->assertSame('John Doe', $client->name());
        $this->assertSame('79998887766', (string) $client->phone());
        $this->assertSame('john@example.com', (string) $client->email());
        $this->assertTrue($repo->existsByPhone('+79998887766'));
        $this->assertTrue($repo->existsByEmail('john@example.com'));
    }

    public function test_fails_on_duplicate_phone(): void
    {
        $this->expectException(LogicException::class);

        $repo = new InMemoryClientRepository();
        $hasher = new BcryptHasher();
        $useCase = new RegisterClientUseCase($repo, $hasher);

        $dto = new RegisterDTO(
            name: 'John Doe',
            phone: '+79998887766',
            email: null,
            birthDate: null,
            password: 'secret',
            consentPersonalData: true,
            consentMarketing: false,
        );

        $useCase->execute($dto);
        $useCase->execute($dto);
    }
}

