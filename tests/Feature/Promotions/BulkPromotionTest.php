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

test('bulk promotion updates student current class and academic year', function () {
    $institution = Institution::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Academiq Test School',
        'code' => 'ATS-01',
        'status' => 'active',
    ]);

    $admin = User::create([
        'tenant_id' => $institution->uuid,
        'uuid' => (string) Str::uuid(),
        'institution_id' => $institution->id,
        'username' => 'admin_bulk_promo',
        'email' => 'admin.bulkpromo@example.com',
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
        'username' => 'student_bulk_1',
        'email' => 'student.bulk1@example.com',
        'password' => Hash::make('password'),
        'role' => 'student',
        'first_name' => 'Student',
        'last_name' => 'One',
        'is_active' => true,
    ]);

    $student = Student::create([
        'tenant_id' => $institution->uuid,
        'institution_id' => $institution->id,
        'uuid' => (string) Str::uuid(),
        'user_id' => $studentUser->id,
        'first_name' => 'Student',
        'last_name' => 'One',
        'email' => $studentUser->email,
        'section_id' => $fromSection->id,
        'class_id' => $fromClass->id,
        'academic_year_id' => $fromAcademicYear->id,
        'roll_number' => 'G5-A-001',
        'admission_number' => 'ADM-BULK-001',
        'admission_date' => '2025-04-10',
        'status' => 'active',
    ]);

    $this->actingAs($admin);

    Livewire::test('pages::app.promotions.bulk-create', ['studentIds' => [$student->id]])
        ->set('to_class_id', (string) $toClass->id)
        ->set('to_academic_year_id', (string) $toAcademicYear->id)
        ->set('status', 'promoted')
        ->set('remarks', 'Promoted in bulk test')
        ->call('save')
        ->assertHasNoErrors();

    $promotion = StudentPromotion::query()
        ->where('tenant_id', $institution->uuid)
        ->where('student_id', $student->id)
        ->first();

    expect($promotion)->not->toBeNull();
    expect($promotion?->from_class_id)->toBe($fromClass->id);
    expect($promotion?->to_class_id)->toBe($toClass->id);
    expect($promotion?->from_academic_year_id)->toBe($fromAcademicYear->id);
    expect($promotion?->to_academic_year_id)->toBe($toAcademicYear->id);

    expect($student->refresh()->class_id)->toBe($toClass->id);
    expect($student->refresh()->academic_year_id)->toBe($toAcademicYear->id);
});
