<?php

namespace Tests\Unit\Client;

use App\Application\Client\Command\RegisterClientUseCase;
use App\Application\Client\Command\UpdateClientUseCase;
use App\Application\Client\DTO\RegisterDTO;
use App\Application\Client\DTO\UpdateClientDTO;
use Illuminate\Hashing\BcryptHasher;
use LogicException;
use PHPUnit\Framework\TestCase;

final class UpdateClientUseCaseTest extends TestCase
{
    public function test_updates_basic_fields_without_changing_password(): void
    {
        $repo = new InMemoryClientRepository();
        $hasher = new BcryptHasher();

        $register = new RegisterClientUseCase($repo, $hasher);
        $update = new UpdateClientUseCase($repo, $hasher);

        $client = $register->execute(new RegisterDTO(
            name: 'John',
            phone: '+7 (999) 111-22-33',
            email: 'old@example.com',
            birthDate: '1990-01-01',
            password: 'secret',
            consentPersonalData: true,
            consentMarketing: false,
        ));

        $updated = $update->execute($client->id(), new UpdateClientDTO(
            name: 'Johnny',
            phone: '+7 (999) 222-33-44',
            email: 'new@example.com',
            birthDate: '1991-02-02',
            consentPersonalData: false,
            consentMarketing: true,
        ));

        $this->assertSame('Johnny', $updated->name());
        $this->assertSame('79992223344', (string) $updated->phone());
        $this->assertSame('new@example.com', (string) $updated->email());
        $this->assertTrue($updated->consentMarketing());
        $this->assertFalse($updated->consentPersonalData());
    }

    public function test_throws_on_duplicate_phone(): void
    {
        $this->expectException(LogicException::class);

        $repo = new InMemoryClientRepository();
        $hasher = new BcryptHasher();

        $register = new RegisterClientUseCase($repo, $hasher);
        $update = new UpdateClientUseCase($repo, $hasher);

        $client1 = $register->execute(new RegisterDTO(
            name: 'A',
            phone: '+7 (900) 000-00-01',
            email: 'a@example.com',
            birthDate: null,
            password: 'secret',
            consentPersonalData: true,
            consentMarketing: false,
        ));

        $client2 = $register->execute(new RegisterDTO(
            name: 'B',
            phone: '+7 (900) 000-00-02',
            email: 'b@example.com',
            birthDate: null,
            password: 'secret',
            consentPersonalData: true,
            consentMarketing: false,
        ));

        $update->execute($client2->id(), new UpdateClientDTO(
            name: null,
            phone: '+7 (900) 000-00-01',
            email: null,
            birthDate: null,
            consentPersonalData: null,
            consentMarketing: null,
        ));
    }
}

