<?php

namespace Tests\Unit\Staff;

use App\Application\Staff\Command\LoginEmployeeUseCase;
use App\Application\Staff\Presenter\EmployeePresenter;
use App\Domain\Staff\Entity\Employee;
use App\Domain\Staff\Enum\StaffRole;
use App\Domain\Staff\Port\StaffSessionPort;
use App\Domain\Staff\Repository\EmployeeRepository;
use DateTimeImmutable;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class LoginEmployeeUseCaseTest extends TestCase
{
    public function test_login_succeeds_for_active_employee(): void
    {
        $employee = Employee::restore(
            id: 5,
            name: 'Анна',
            email: 'anna@gangsta.local',
            phone: null,
            role: StaffRole::Administrator,
            passwordHash: password_hash('secret12', PASSWORD_BCRYPT),
            isActive: true,
            createdAt: new DateTimeImmutable('2026-11-13 12:00:00'),
        );

        $repository = $this->createMock(EmployeeRepository::class);
        $repository->expects($this->once())
            ->method('findByEmail')
            ->with('anna@gangsta.local')
            ->willReturn($employee);

        $session = $this->createMock(StaffSessionPort::class);
        $session->expects($this->once())->method('login')->with(5)->willReturn('test-token');

        $useCase = new LoginEmployeeUseCase($repository, $session, new EmployeePresenter());
        $result = $useCase->execute('anna@gangsta.local', 'secret12');

        $this->assertSame('test-token', $result['token']);
        $this->assertSame(5, $result['employee']['id']);
        $this->assertSame('administrator', $result['employee']['role']);
    }

    public function test_login_rejects_inactive_employee(): void
    {
        $employee = Employee::restore(
            id: 5,
            name: 'Анна',
            email: 'anna@gangsta.local',
            phone: null,
            role: StaffRole::Administrator,
            passwordHash: password_hash('secret12', PASSWORD_BCRYPT),
            isActive: false,
            createdAt: new DateTimeImmutable('2026-11-13 12:00:00'),
        );

        $repository = $this->createMock(EmployeeRepository::class);
        $repository->method('findByEmail')->willReturn($employee);

        $session = $this->createMock(StaffSessionPort::class);
        $session->expects($this->never())->method('login');

        $useCase = new LoginEmployeeUseCase($repository, $session, new EmployeePresenter());

        $this->expectException(ValidationException::class);
        $useCase->execute('anna@gangsta.local', 'secret12');
    }

    public function test_login_rejects_bad_password(): void
    {
        $employee = Employee::restore(
            id: 5,
            name: 'Анна',
            email: 'anna@gangsta.local',
            phone: null,
            role: StaffRole::Courier,
            passwordHash: password_hash('secret12', PASSWORD_BCRYPT),
            isActive: true,
            createdAt: new DateTimeImmutable('2026-11-13 12:00:00'),
        );

        $repository = $this->createMock(EmployeeRepository::class);
        $repository->method('findByEmail')->willReturn($employee);

        $session = $this->createMock(StaffSessionPort::class);
        $session->expects($this->never())->method('login');

        $useCase = new LoginEmployeeUseCase($repository, $session, new EmployeePresenter());

        $this->expectException(ValidationException::class);
        $useCase->execute('anna@gangsta.local', 'wrong');
    }
}
