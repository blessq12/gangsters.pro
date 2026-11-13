<?php

namespace Tests\Unit\Staff;

use App\Domain\Staff\Entity\Employee;
use App\Domain\Staff\Enum\StaffRole;
use App\Domain\Staff\Event\EmployeeDeactivated;
use App\Domain\Staff\Event\EmployeeHired;
use App\Domain\Staff\Event\EmployeePasswordChanged;
use App\Domain\Staff\Event\EmployeeReactivated;
use App\Domain\Staff\Event\EmployeeRoleChanged;
use DateTimeImmutable;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\TestCase;

final class EmployeeTest extends TestCase
{
    public function test_hire_creates_active_employee(): void
    {
        $employee = Employee::hire(
            name: '  Анна  ',
            email: '  anna@gangsta.local  ',
            phone: '  +7 (900) 000-00-00  ',
            role: StaffRole::Administrator,
            passwordHash: 'hash-1',
        );

        $this->assertFalse($employee->hasId());
        $this->assertSame('Анна', $employee->name());
        $this->assertSame('anna@gangsta.local', $employee->email());
        $this->assertSame('+7 (900) 000-00-00', $employee->phone());
        $this->assertSame(StaffRole::Administrator, $employee->role());
        $this->assertSame('hash-1', $employee->passwordHash());
        $this->assertTrue($employee->isActive());
    }

    public function test_hire_rejects_empty_name_email_or_password_hash(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Employee::hire(
            name: ' ',
            email: 'a@b.c',
            phone: null,
            role: StaffRole::Courier,
            passwordHash: 'hash',
        );
    }

    public function test_hire_normalizes_blank_phone_to_null(): void
    {
        $employee = Employee::hire(
            name: 'Курьер',
            email: 'courier@gangsta.local',
            phone: '   ',
            role: StaffRole::Courier,
            passwordHash: 'hash',
        );

        $this->assertNull($employee->phone());
    }

    public function test_change_role_updates_role(): void
    {
        $employee = $this->restored();
        $employee->changeRole(StaffRole::Kitchen);

        $this->assertSame(StaffRole::Kitchen, $employee->role());
    }

    public function test_change_role_rejects_same_role(): void
    {
        $employee = $this->restored();

        $this->expectException(InvalidArgumentException::class);
        $employee->changeRole(StaffRole::Administrator);
    }

    public function test_deactivate_and_reactivate(): void
    {
        $employee = $this->restored();
        $employee->deactivate();
        $this->assertFalse($employee->isActive());

        $employee->reactivate();
        $this->assertTrue($employee->isActive());
    }

    public function test_deactivate_twice_fails(): void
    {
        $employee = $this->restored();
        $employee->deactivate();

        $this->expectException(InvalidArgumentException::class);
        $employee->deactivate();
    }

    public function test_events_require_saved_employee(): void
    {
        $employee = Employee::hire(
            name: 'Новый',
            email: 'new@gangsta.local',
            phone: null,
            role: StaffRole::Owner,
            passwordHash: 'hash',
        );

        $this->expectException(LogicException::class);
        EmployeeHired::fromEmployee($employee);
    }

    public function test_event_factories_after_assign_id(): void
    {
        $employee = Employee::hire(
            name: 'Новый',
            email: 'new@gangsta.local',
            phone: null,
            role: StaffRole::Owner,
            passwordHash: 'hash',
        );
        $employee->assignId(10);

        $hired = EmployeeHired::fromEmployee($employee);
        $this->assertSame(10, $hired->employeeId());
        $this->assertSame('new@gangsta.local', $hired->email());
        $this->assertSame(StaffRole::Owner, $hired->role());

        $from = StaffRole::Owner;
        $employee->changeRole(StaffRole::Administrator);
        $roleChanged = EmployeeRoleChanged::fromEmployee($employee, $from);
        $this->assertSame($from, $roleChanged->from());
        $this->assertSame(StaffRole::Administrator, $roleChanged->to());

        $employee->changePassword('hash-2');
        $passwordChanged = EmployeePasswordChanged::fromEmployee($employee);
        $this->assertSame(10, $passwordChanged->employeeId());

        $employee->deactivate();
        $this->assertSame(10, EmployeeDeactivated::fromEmployee($employee)->employeeId());

        $employee->reactivate();
        $this->assertSame(10, EmployeeReactivated::fromEmployee($employee)->employeeId());
    }

    private function restored(): Employee
    {
        return Employee::restore(
            id: 1,
            name: 'Менеджер',
            email: 'manager@gangsta.local',
            phone: null,
            role: StaffRole::Administrator,
            passwordHash: 'hash',
            isActive: true,
            createdAt: new DateTimeImmutable('2026-11-13 12:00:00'),
        );
    }
}
