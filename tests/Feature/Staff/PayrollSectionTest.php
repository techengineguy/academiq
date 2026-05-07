<?php

use App\Models\Institution;
use App\Models\Payroll;
use App\Models\PayrollAllowance;
use App\Models\PayrollDeduction;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createPayrollStaffFixture(): array
{
    $institution = Institution::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Academiq School',
        'code' => 'AQ-SCH-01',
        'status' => 'active',
    ]);

    $admin = User::create([
        'tenant_id' => $institution->uuid,
        'uuid' => (string) Str::uuid(),
        'institution_id' => $institution->id,
        'username' => 'admin1',
        'email' => 'admin@example.com',
        'password' => Hash::make('password'),
        'role' => 'admin',
        'first_name' => 'Admin',
        'last_name' => 'User',
        'is_active' => true,
    ]);

    $teacherUser = User::create([
        'tenant_id' => $institution->uuid,
        'uuid' => (string) Str::uuid(),
        'institution_id' => $institution->id,
        'username' => 'teacher1',
        'email' => 'teacher@example.com',
        'password' => Hash::make('password'),
        'role' => 'teacher',
        'first_name' => 'Teacher',
        'last_name' => 'One',
        'is_active' => true,
    ]);

    $teacher = Teacher::create([
        'tenant_id' => $institution->uuid,
        'uuid' => (string) Str::uuid(),
        'user_id' => $teacherUser->id,
        'institution_id' => $institution->id,
        'first_name' => 'Teacher',
        'last_name' => 'One',
        'email' => 'teacher@example.com',
        'employee_id' => 'TCH-001',
        'joining_date' => '2025-01-10',
        'designation' => 'Teacher',
        'department' => 'Academic',
        'qualification' => 'B.Ed',
        'specialization' => 'Mathematics',
        'salary' => '20000.00',
        'employment_type' => 'full-time',
        'status' => 'active',
    ]);

    return [$institution, $admin, $teacherUser, $teacher];
}

test('payroll create form stores payroll with allowances and deductions', function () {
    [$institution, $admin, $teacherUser] = createPayrollStaffFixture();

    $this->actingAs($admin);

    Livewire::test('pages::app.staff.payroll.create')
        ->set('user_id', (string) $teacherUser->id)
        ->set('month', '2026-05')
        ->set('basic_salary', '20000')
        ->set('tax', '500')
        ->set('payment_date', '2026-05-31')
        ->set('status', 'paid')
        ->set('remarks', 'May payroll')
        ->set('allowances', [
            ['type' => 'Transport', 'amount' => '1000', 'description' => 'Monthly transport'],
            ['type' => 'Housing', 'amount' => '500', 'description' => 'Housing support'],
        ])
        ->set('deductions', [
            ['type' => 'Late Fine', 'amount' => '250', 'description' => 'Late arrival'],
        ])
        ->call('save')
        ->assertHasNoErrors();

    $payroll = Payroll::where('tenant_id', $institution->uuid)->first();

    expect($payroll)->not->toBeNull();
    expect($payroll?->net_salary)->toBe('20750.00');

    $this->assertDatabaseHas('payrolls', [
        'tenant_id' => $institution->uuid,
        'user_id' => $teacherUser->id,
        'month' => '2026-05',
        'basic_salary' => '20000.00',
        'allowances' => '1500.00',
        'deductions' => '250.00',
        'tax' => '500.00',
        'net_salary' => '20750.00',
        'status' => 'paid',
    ]);

    $this->assertDatabaseCount('payroll_allowances', 2);
    $this->assertDatabaseCount('payroll_deductions', 1);
});

test('payroll edit form updates payroll and replaces child rows', function () {
    [$institution, $admin, $teacherUser] = createPayrollStaffFixture();

    $this->actingAs($admin);

    $payroll = Payroll::create([
        'tenant_id' => $institution->uuid,
        'uuid' => (string) Str::uuid(),
        'user_id' => $teacherUser->id,
        'month' => '2026-05',
        'basic_salary' => '20000.00',
        'allowances' => '1000.00',
        'deductions' => '100.00',
        'tax' => '500.00',
        'net_salary' => '20400.00',
        'payment_date' => '2026-05-31',
        'status' => 'pending',
        'remarks' => 'Initial payroll',
        'processed_by' => $admin->id,
    ]);

    PayrollAllowance::create([
        'tenant_id' => $institution->uuid,
        'uuid' => (string) Str::uuid(),
        'payroll_id' => $payroll->id,
        'type' => 'Transport',
        'amount' => '1000.00',
        'description' => 'Initial allowance',
    ]);

    PayrollDeduction::create([
        'tenant_id' => $institution->uuid,
        'uuid' => (string) Str::uuid(),
        'payroll_id' => $payroll->id,
        'type' => 'Late Fine',
        'amount' => '100.00',
        'description' => 'Initial deduction',
    ]);

    Livewire::test('pages::app.staff.payroll.edit', ['id' => $payroll->id])
        ->set('month', '2026-06')
        ->set('basic_salary', '22000')
        ->set('tax', '600')
        ->set('payment_date', '2026-06-30')
        ->set('status', 'paid')
        ->set('remarks', 'Updated payroll')
        ->set('allowances', [
            ['type' => 'Transport', 'amount' => '1500', 'description' => 'Updated transport'],
        ])
        ->set('deductions', [
            ['type' => 'Late Fine', 'amount' => '200', 'description' => 'Updated deduction'],
        ])
        ->call('update')
        ->assertHasNoErrors();

    $payroll->refresh();

    expect($payroll->month)->toBe('2026-06');
    expect($payroll->basic_salary)->toBe('22000.00');
    expect($payroll->allowances)->toBe('1500.00');
    expect($payroll->deductions)->toBe('200.00');
    expect($payroll->net_salary)->toBe('22700.00');

    $this->assertDatabaseCount('payroll_allowances', 1);
    $this->assertDatabaseCount('payroll_deductions', 1);
});