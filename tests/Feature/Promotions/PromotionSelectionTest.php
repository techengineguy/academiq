<?php

use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\Institution;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('promotion page hides students until an academic year is selected', function () {
    $institution = Institution::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Academiq Hidden Students Test School',
        'code' => 'AHSTS-01',
        'status' => 'active',
    ]);

    $admin = User::create([
        'tenant_id' => $institution->uuid,
        'uuid' => (string) Str::uuid(),
        'institution_id' => $institution->id,
        'username' => 'admin_hidden_students_test',
        'email' => 'admin.hidden@example.com',
        'password' => Hash::make('password'),
        'role' => 'admin',
        'first_name' => 'Admin',
        'last_name' => 'User',
        'is_active' => true,
    ]);

    $academicYear = AcademicYear::create([
        'tenant_id' => $institution->uuid,
        'uuid' => (string) Str::uuid(),
        'institution_id' => $institution->id,
        'name' => '2025-2026',
        'start_date' => '2025-04-01',
        'end_date' => '2026-03-31',
        'status' => 'active',
    ]);

    $class = ClassModel::create([
        'tenant_id' => $institution->uuid,
        'uuid' => (string) Str::uuid(),
        'institution_id' => $institution->id,
        'academic_year_id' => $academicYear->id,
        'name' => 'Grade 5',
        'code' => 'G5',
        'status' => 'active',
    ]);

    $studentUser = User::create([
        'tenant_id' => $institution->uuid,
        'uuid' => (string) Str::uuid(),
        'institution_id' => $institution->id,
        'username' => 'student_hidden_1',
        'email' => 'student.hidden1@example.com',
        'password' => Hash::make('password'),
        'role' => 'student',
        'first_name' => 'Student',
        'last_name' => 'Hidden',
        'is_active' => true,
    ]);

    $student = Student::create([
        'tenant_id' => $institution->uuid,
        'institution_id' => $institution->id,
        'uuid' => (string) Str::uuid(),
        'user_id' => $studentUser->id,
        'first_name' => 'Student',
        'last_name' => 'Hidden',
        'email' => $studentUser->email,
        'class_id' => $class->id,
        'academic_year_id' => $academicYear->id,
        'roll_number' => 'ADM-HIDDEN-001',
        'admission_number' => 'ADM-HIDDEN-001',
        'admission_date' => '2025-04-10',
        'status' => 'active',
    ]);

    $this->actingAs($admin);

    Livewire::test('pages::app.promotions.index')
        ->assertDontSee($student->admission_number)
        ->assertSet('students.total', 0);
});

test('promotion row selection keeps the select-all checkbox in sync', function () {
    $institution = Institution::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Academiq Selection Test School',
        'code' => 'ASTS-01',
        'status' => 'active',
    ]);

    $admin = User::create([
        'tenant_id' => $institution->uuid,
        'uuid' => (string) Str::uuid(),
        'institution_id' => $institution->id,
        'username' => 'admin_selection_test',
        'email' => 'admin.selection@example.com',
        'password' => Hash::make('password'),
        'role' => 'admin',
        'first_name' => 'Admin',
        'last_name' => 'User',
        'is_active' => true,
    ]);

    $academicYear = AcademicYear::create([
        'tenant_id' => $institution->uuid,
        'uuid' => (string) Str::uuid(),
        'institution_id' => $institution->id,
        'name' => '2025-2026',
        'start_date' => '2025-04-01',
        'end_date' => '2026-03-31',
        'status' => 'active',
    ]);

    $class = ClassModel::create([
        'tenant_id' => $institution->uuid,
        'uuid' => (string) Str::uuid(),
        'institution_id' => $institution->id,
        'academic_year_id' => $academicYear->id,
        'name' => 'Grade 5',
        'code' => 'G5',
        'status' => 'active',
    ]);

    $students = collect([
        ['username' => 'student_selection_1', 'email' => 'student.selection1@example.com', 'admission' => 'ADM-SEL-001'],
        ['username' => 'student_selection_2', 'email' => 'student.selection2@example.com', 'admission' => 'ADM-SEL-002'],
    ])->map(function (array $data) use ($institution, $class, $academicYear): Student {
        $user = User::create([
            'tenant_id' => $institution->uuid,
            'uuid' => (string) Str::uuid(),
            'institution_id' => $institution->id,
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make('password'),
            'role' => 'student',
            'first_name' => 'Student',
            'last_name' => 'Selection',
            'is_active' => true,
        ]);

        return Student::create([
            'tenant_id' => $institution->uuid,
            'institution_id' => $institution->id,
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'first_name' => 'Student',
            'last_name' => 'Selection',
            'email' => $user->email,
            'class_id' => $class->id,
            'academic_year_id' => $academicYear->id,
            'roll_number' => $data['admission'],
            'admission_number' => $data['admission'],
            'admission_date' => '2025-04-10',
            'status' => 'active',
        ]);
    });

    $this->actingAs($admin);

    Livewire::test('pages::app.promotions.index')
        ->set('filterAcademicYear', (string) $academicYear->id)
        ->set('selectedStudents', [$students[0]->id])
        ->assertSet('selectAll', false)
        ->set('selectedStudents', $students->pluck('id')->all())
        ->assertSet('selectAll', true)
        ->set('selectAll', false)
        ->assertSet('selectedStudents', []);
});
