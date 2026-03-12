<?php

namespace Tests\Unit\Client;

use App\Application\Client\Command\ChangePasswordUseCase;
use App\Application\Client\Command\LoginClientUseCase;
use App\Application\Client\Command\RegisterClientUseCase;
use App\Application\Client\Command\RequestPasswordResetUseCase;
use App\Application\Client\DTO\ChangePasswordDTO;
use App\Application\Client\DTO\LoginDTO;
use App\Application\Client\DTO\RegisterDTO;
use App\Application\Client\DTO\RequestPasswordResetDTO;
use Illuminate\Hashing\BcryptHasher;
use PHPUnit\Framework\TestCase;

final class PasswordUseCasesTest extends TestCase
{
    public function test_password_reset_flow(): void
    {
        $repo = new InMemoryClientRepository();
        $hasher = new BcryptHasher();

        $register = new RegisterClientUseCase($repo, $hasher);
        $requestReset = new RequestPasswordResetUseCase($repo, $hasher);
        $changePassword = new ChangePasswordUseCase($repo, $hasher);
        $login = new LoginClientUseCase($repo, $hasher);

        $register->execute(new RegisterDTO(
            name: 'John',
            phone: '+7 (999) 111-22-33',
            email: 'john@example.com',
            birthDate: null,
            password: 'old-secret',
            consentPersonalData: true,
            consentMarketing: false,
        ));

        $token = $requestReset->execute(new RequestPasswordResetDTO(
            email: 'john@example.com',
        ));

        $this->assertNotEmpty($token);

        $changePassword->execute(new ChangePasswordDTO(
            token: $token,
            password: 'new-secret',
        ));

        $client = $login->execute(new LoginDTO(
            phone: null,
            email: 'john@example.com',
            password: 'new-secret',
        ));

        $this->assertSame('john@example.com', (string) $client->email());
    }
}

