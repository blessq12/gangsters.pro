<?php

namespace Tests\Unit\Application\Operations\Client;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Operations\Client\Command\CreateAdminClientUseCase;
use App\Application\Operations\Client\DTO\CreateAdminClientDTO;
use App\Application\Operations\Client\Presenter\AdminClientPresenter;
use App\Domain\Client\Entity\Client;
use App\Domain\Client\Events\ClientRegistered;
use App\Domain\Client\Factory\ClientFactory;
use App\Domain\Client\Repository\ClientRepository;
use App\Shared\Events\DomainEventBus;
use Illuminate\Contracts\Hashing\Hasher;
use Mockery;
use PHPUnit\Framework\TestCase;

final class CreateAdminClientUseCaseTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_creates_client_and_publishes_event(): void
    {
        $repository = Mockery::mock(ClientRepository::class);
        $repository->shouldReceive('existsByPhone')->once()->with('79990000001')->andReturn(false);
        $repository->shouldReceive('existsByEmail')->once()->with('new@example.com')->andReturn(false);
        $repository->shouldReceive('save')->once()->with(Mockery::on(function (Client $client): bool {
            $client->assignPersistedId(42);

            return $client->name() === 'Новый клиент'
                && (string) $client->phone() === '9990000001'
                && $client->status() === Client::STATUS_ACTIVE;
        }));

        $hasher = Mockery::mock(Hasher::class);
        $hasher->shouldReceive('make')->once()->with('secret123')->andReturn('hashed');

        $events = Mockery::mock(DomainEventBus::class);
        $events->shouldReceive('publish')->once()->with(Mockery::type(ClientRegistered::class));

        $useCase = new CreateAdminClientUseCase(
            $repository,
            new ClientFactory,
            $hasher,
            new AdminClientPresenter,
            $events,
        );

        $result = $useCase->execute(new CreateAdminClientDTO(
            name: 'Новый клиент',
            phone: '79990000001',
            email: 'new@example.com',
            birthDate: '1990-05-15',
            password: 'secret123',
            consentPersonalData: true,
            consentMarketing: false,
        ));

        $this->assertSame(42, $result['client']['id']);
        $this->assertSame('Новый клиент', $result['client']['name']);
        $this->assertSame('new@example.com', $result['client']['email']);
    }

    public function test_rejects_duplicate_phone(): void
    {
        $repository = Mockery::mock(ClientRepository::class);
        $repository->shouldReceive('existsByPhone')->once()->with('79990000001')->andReturn(true);

        $useCase = new CreateAdminClientUseCase(
            $repository,
            new ClientFactory,
            Mockery::mock(Hasher::class),
            new AdminClientPresenter,
            Mockery::mock(DomainEventBus::class),
        );

        try {
            $useCase->execute(new CreateAdminClientDTO(
                name: 'Клиент',
                phone: '79990000001',
                email: null,
                birthDate: null,
                password: null,
                consentPersonalData: false,
                consentMarketing: false,
            ));
            $this->fail('Expected ApiException was not thrown.');
        } catch (ApiException $exception) {
            $this->assertSame(422, $exception->statusCode());
            $this->assertSame('Клиент с таким телефоном уже существует.', $exception->getMessage());
        }
    }

    public function test_rejects_duplicate_email(): void
    {
        $repository = Mockery::mock(ClientRepository::class);
        $repository->shouldReceive('existsByPhone')->once()->with('79990000002')->andReturn(false);
        $repository->shouldReceive('existsByEmail')->once()->with('taken@example.com')->andReturn(true);

        $useCase = new CreateAdminClientUseCase(
            $repository,
            new ClientFactory,
            Mockery::mock(Hasher::class),
            new AdminClientPresenter,
            Mockery::mock(DomainEventBus::class),
        );

        try {
            $useCase->execute(new CreateAdminClientDTO(
                name: 'Клиент',
                phone: '79990000002',
                email: 'taken@example.com',
                birthDate: null,
                password: null,
                consentPersonalData: false,
                consentMarketing: false,
            ));
            $this->fail('Expected ApiException was not thrown.');
        } catch (ApiException $exception) {
            $this->assertSame(422, $exception->statusCode());
            $this->assertSame('Клиент с таким email уже существует.', $exception->getMessage());
        }
    }

    public function test_skips_email_uniqueness_when_email_empty(): void
    {
        $repository = Mockery::mock(ClientRepository::class);
        $repository->shouldReceive('existsByPhone')->once()->andReturn(false);
        $repository->shouldNotReceive('existsByEmail');
        $repository->shouldReceive('save')->once()->with(Mockery::on(function (Client $client): bool {
            $client->assignPersistedId(7);

            return true;
        }));

        $events = Mockery::mock(DomainEventBus::class);
        $events->shouldReceive('publish')->once();

        $useCase = new CreateAdminClientUseCase(
            $repository,
            new ClientFactory,
            Mockery::mock(Hasher::class),
            new AdminClientPresenter,
            $events,
        );

        $result = $useCase->execute(new CreateAdminClientDTO(
            name: 'Без email',
            phone: '79990000003',
            email: null,
            birthDate: null,
            password: null,
            consentPersonalData: false,
            consentMarketing: false,
        ));

        $this->assertSame(7, $result['client']['id']);
        $this->assertNull($result['client']['email']);
    }
}
