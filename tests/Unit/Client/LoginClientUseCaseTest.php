<?php

namespace Tests\Unit\Client;

use App\Application\Client\Command\LoginClientUseCase;
use App\Application\Client\Command\RegisterClientUseCase;
use App\Application\Client\DTO\LoginDTO;
use App\Application\Client\DTO\RegisterDTO;
use Illuminate\Hashing\BcryptHasher;
use LogicException;
use PHPUnit\Framework\TestCase;

final class LoginClientUseCaseTest extends TestCase
{
    public function test_login_by_phone(): void
    {
        $repo = new InMemoryClientRepository();
        $hasher = new BcryptHasher();

        $register = new RegisterClientUseCase($repo, $hasher);
        $login = new LoginClientUseCase($repo, $hasher);

        $register->execute(new RegisterDTO(
            name: 'John Doe',
            phone: '+79998887766',
            email: null,
            birthDate: null,
            password: 'secret',
            consentPersonalData: true,
            consentMarketing: false,
        ));

        $client = $login->execute(new LoginDTO(
            phone: '+79998887766',
            email: null,
            password: 'secret',
        ));

        $this->assertSame('John Doe', $client->name());
    }

    public function test_login_by_email(): void
    {
        $repo = new InMemoryClientRepository();
        $hasher = new BcryptHasher();

        $register = new RegisterClientUseCase($repo, $hasher);
        $login = new LoginClientUseCase($repo, $hasher);

        $register->execute(new RegisterDTO(
            name: 'Jane Doe',
            phone: '+79990001122',
            email: 'jane@example.com',
            birthDate: null,
            password: 'secret',
            consentPersonalData: true,
            consentMarketing: false,
        ));

        $client = $login->execute(new LoginDTO(
            phone: null,
            email: 'jane@example.com',
            password: 'secret',
        ));

        $this->assertSame('Jane Doe', $client->name());
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $this->expectException(LogicException::class);

        $repo = new InMemoryClientRepository();
        $hasher = new BcryptHasher();

        $register = new RegisterClientUseCase($repo, $hasher);
        $login = new LoginClientUseCase($repo, $hasher);

        $register->execute(new RegisterDTO(
            name: 'John Doe',
            phone: '+79998887766',
            email: null,
            birthDate: null,
            password: 'secret',
            consentPersonalData: true,
            consentMarketing: false,
        ));

        $login->execute(new LoginDTO(
            phone: '+79998887766',
            email: null,
            password: 'wrong',
        ));
    }
}

