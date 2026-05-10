<?php

use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\Institution;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentPromotion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('editing a promotion syncs student current class and academic year', function () {
    $institution = Institution::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Academiq Edit Test School',
        'code' => 'AETS-01',
        'status' => 'active',
    ]);

    $admin = User::create([
        'tenant_id' => $institution->uuid,
        'uuid' => (string) Str::uuid(),
        'institution_id' => $institution->id,
        'username' => 'admin_edit_promo',
        'email' => 'admin.editpromo@example.com',
        'password' => Hash::make('password'),
        'role' => 'admin',
        'first_name' => 'Admin',
        'last_name' => 'User',
        'is_active' => true,
    ]);

    $fromAcademicYear = AcademicYear::create([
        'tenant_id' => $institution->uuid,
        'uuid' => (string) Str::uuid(),
        'institution_id' => $institution->id,
        'name' => '2025-2026',
        'start_date' => '2025-04-01',
        'end_date' => '2026-03-31',
        'status' => 'active',
    ]);

    $toAcademicYear = AcademicYear::create([
        'tenant_id' => $institution->uuid,
        'uuid' => (string) Str::uuid(),
        'institution_id' => $institution->id,
        'name' => '2026-2027',
        'start_date' => '2026-04-01',
        'end_date' => '2027-03-31',
        'status' => 'active',
    ]);

    $fromClass = ClassModel::create([
        'tenant_id' => $institution->uuid,
        'uuid' => (string) Str::uuid(),
        'institution_id' => $institution->id,
        'academic_year_id' => $fromAcademicYear->id,
        'name' => 'Grade 5',
        'code' => 'G5',
        'status' => 'active',
    ]);

    $toClass = ClassModel::create([
        'tenant_id' => $institution->uuid,
        'uuid' => (string) Str::uuid(),
        'institution_id' => $institution->id,
        'academic_year_id' => $toAcademicYear->id,
        'name' => 'Grade 6',
        'code' => 'G6',
        'status' => 'active',
    ]);

    $fromSection = Section::create([
        'tenant_id' => $institution->uuid,
        'uuid' => (string) Str::uuid(),
        'class_id' => $fromClass->id,
        'name' => 'A',
        'status' => 'active',
    ]);

    $studentUser = User::create([
        'tenant_id' => $institution->uuid,
        'uuid' => (string) Str::uuid(),
        'institution_id' => $institution->id,
        'username' => 'student_edit_1',
        'email' => 'student.edit1@example.com',
        'password' => Hash::make('password'),
        'role' => 'student',
        'first_name' => 'Student',
        'last_name' => 'Edit',
        'is_active' => true,
    ]);

    $student = Student::create([
        'tenant_id' => $institution->uuid,
        'institution_id' => $institution->id,
        'uuid' => (string) Str::uuid(),
        'user_id' => $studentUser->id,
        'first_name' => 'Student',
        'last_name' => 'Edit',
        'email' => $studentUser->email,
        'section_id' => $fromSection->id,
        'class_id' => $fromClass->id,
        'academic_year_id' => $fromAcademicYear->id,
        'roll_number' => 'G5-A-002',
        'admission_number' => 'ADM-EDIT-001',
        'admission_date' => '2025-04-10',
        'status' => 'active',
    ]);

    $promotion = StudentPromotion::create([
        'tenant_id' => $institution->uuid,
        'uuid' => (string) Str::uuid(),
        'student_id' => $student->id,
        'from_class_id' => $fromClass->id,
        'to_class_id' => $toClass->id,
        'from_section_id' => $fromSection->id,
        'to_section_id' => null,
        'from_academic_year_id' => $fromAcademicYear->id,
        'to_academic_year_id' => $toAcademicYear->id,
        'status' => 'promoted',
        'remarks' => 'Initial promotion',
        'processed_by' => $admin->id,
    ]);

    $this->actingAs($admin);

    Livewire::test('pages::app.promotions.edit')
        ->call('loadPromotion', $promotion->uuid)
        ->set('student_id', (string) $student->id)
        ->set('from_class_id', (string) $fromClass->id)
        ->set('to_class_id', (string) $toClass->id)
        ->set('from_academic_year_id', (string) $fromAcademicYear->id)
        ->set('to_academic_year_id', (string) $toAcademicYear->id)
        ->set('status', 'promoted')
        ->set('remarks', 'Edited promotion')
        ->call('update')
        ->assertHasNoErrors();

    expect($student->refresh()->class_id)->toBe($toClass->id);
    expect($student->refresh()->academic_year_id)->toBe($toAcademicYear->id);

    expect($promotion->refresh()->remarks)->toBe('Edited promotion');
});
