<?php

namespace Tests\Unit\Application\Operations\Client;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Operations\Client\Command\BlockAdminClientUseCase;
use App\Application\Operations\Client\Command\UnblockAdminClientUseCase;
use App\Application\Operations\Client\Presenter\AdminClientPresenter;
use App\Domain\Client\Entity\Client;
use App\Domain\Client\Repository\ClientRepository;
use App\Domain\Client\VO\Email;
use App\Domain\Client\VO\PhoneNumber;
use App\Shared\Events\DomainEventBus;
use DateTimeImmutable;
use Mockery;
use PHPUnit\Framework\TestCase;

final class BlockAdminClientUseCaseTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_blocks_active_client(): void
    {
        $client = $this->activeClient();
        $repository = Mockery::mock(ClientRepository::class);
        $repository->shouldReceive('findById')->once()->with(1)->andReturn($client);
        $repository->shouldReceive('save')->once()->with($client);

        $events = Mockery::mock(DomainEventBus::class);
        $events->shouldReceive('publish')->once();

        $useCase = new BlockAdminClientUseCase($repository, new AdminClientPresenter, $events);
        $result = $useCase->execute(1);

        $this->assertSame(Client::STATUS_BLOCKED, $client->status());
        $this->assertSame(Client::STATUS_BLOCKED, $result['client']['status']);
    }

    public function test_unblocks_blocked_client(): void
    {
        $client = $this->activeClient();
        $client->block();

        $repository = Mockery::mock(ClientRepository::class);
        $repository->shouldReceive('findById')->once()->with(1)->andReturn($client);
        $repository->shouldReceive('save')->once()->with($client);

        $events = Mockery::mock(DomainEventBus::class);
        $events->shouldReceive('publish')->once();

        $useCase = new UnblockAdminClientUseCase($repository, new AdminClientPresenter, $events);
        $result = $useCase->execute(1);

        $this->assertSame(Client::STATUS_ACTIVE, $client->status());
        $this->assertSame(Client::STATUS_ACTIVE, $result['client']['status']);
    }

    public function test_block_rejects_already_blocked_client(): void
    {
        $client = $this->activeClient();
        $client->block();

        $repository = Mockery::mock(ClientRepository::class);
        $repository->shouldReceive('findById')->once()->with(1)->andReturn($client);

        $useCase = new BlockAdminClientUseCase(
            $repository,
            new AdminClientPresenter,
            Mockery::mock(DomainEventBus::class),
        );

        $this->expectException(ApiException::class);
        $useCase->execute(1);
    }

    private function activeClient(): Client
    {
        $now = new DateTimeImmutable('2026-01-01 12:00:00');

        return Client::reconstitute(
            id: 1,
            name: 'Test Client',
            phone: new PhoneNumber('79990000001'),
            email: new Email('client@example.com'),
            birthDate: null,
            passwordHash: null,
            status: Client::STATUS_ACTIVE,
            consentPersonalData: true,
            consentMarketing: false,
            defaultAddressId: null,
            addresses: [],
            createdAt: $now,
            updatedAt: $now,
            deletedAt: null,
        );
    }
}
