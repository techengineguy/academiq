<?php

use App\Models\AcademicYear;
use App\Models\AdmissionApplication;
use App\Models\ClassModel;
use App\Models\Institution;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

it('allows guests to view success page with application reference number', function () {
    $institution = Institution::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Test Academy',
        'code' => 'TA',
        'email' => 'admin@testacademy.com',
        'status' => 'active',
    ]);

    $academicYear = AcademicYear::create([
        'tenant_id' => $institution->uuid,
        'uuid' => (string) Str::uuid(),
        'institution_id' => $institution->id,
        'name' => '2026-2027',
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'status' => 'active',
    ]);

    $class = ClassModel::create([
        'tenant_id' => $institution->uuid,
        'uuid' => (string) Str::uuid(),
        'institution_id' => $institution->id,
        'academic_year_id' => $academicYear->id,
        'name' => 'Grade 1',
        'code' => 'G1',
        'status' => 'active',
    ]);

    // Create an application
    $application = AdmissionApplication::create([
        'tenant_id' => $institution->uuid,
        'uuid' => (string) Str::uuid(),
        'institution_id' => $institution->id,
        'academic_year_id' => $academicYear->id,
        'class_id' => $class->id,
        'application_number' => 'APP-ABC123',
        'application_date' => '2026-01-15',
        'student_name' => 'John Doe',
        'date_of_birth' => '2015-01-15',
        'gender' => 'male',
        'father_name' => 'Father Doe',
        'mother_name' => 'Mother Doe',
        'parent_phone' => '1234567890',
        'address' => '123 Main Street',
        'status' => 'submitted',
    ]);

    // Access the success page
    $response = get(route('admissions.success', [
        'institution' => $institution->uuid,
        'application' => $application->uuid,
    ]));

    $response->assertOk();
    $response->assertSeeInHtml('Application Submitted');
    $response->assertSeeInHtml('APP-ABC123'); // Application reference number
    $response->assertSeeInHtml('John Doe');
    $response->assertSeeInHtml('Pending Review');
});

it('prevents access to success page with mismatched institution and application', function () {
    $institution1 = Institution::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Institution 1',
        'code' => 'INST1',
        'status' => 'active',
    ]);

    $institution2 = Institution::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Institution 2',
        'code' => 'INST2',
        'status' => 'active',
    ]);

    $academicYear = AcademicYear::create([
        'tenant_id' => $institution1->uuid,
        'uuid' => (string) Str::uuid(),
        'institution_id' => $institution1->id,
        'name' => '2026-2027',
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'status' => 'active',
    ]);

    $class = ClassModel::create([
        'tenant_id' => $institution1->uuid,
        'uuid' => (string) Str::uuid(),
        'institution_id' => $institution1->id,
        'academic_year_id' => $academicYear->id,
        'name' => 'Grade 1',
        'status' => 'active',
    ]);

    $application = AdmissionApplication::create([
        'tenant_id' => $institution1->uuid,
        'uuid' => (string) Str::uuid(),
        'institution_id' => $institution1->id,
        'academic_year_id' => $academicYear->id,
        'class_id' => $class->id,
        'application_number' => 'APP-123',
        'application_date' => '2026-01-15',
        'student_name' => 'John Doe',
        'date_of_birth' => '2015-01-15',
        'gender' => 'male',
        'father_name' => 'Father Doe',
        'mother_name' => 'Mother Doe',
        'parent_phone' => '1234567890',
        'address' => '123 Main Street',
        'status' => 'submitted',
    ]);

    // Try to access success page with institution2 but application from institution1
    $response = get(route('admissions.success', [
        'institution' => $institution2->uuid,
        'application' => $application->uuid,
    ]));

    $response->assertNotFound(); // Should get 404
});
