<?php

use App\Models\AcademicYear;
use App\Models\AdmissionApplication;
use App\Models\ClassModel;
use App\Models\Institution;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

it('loads success page route', function () {
    $institution = Institution::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Test Academy',
        'code' => 'TA',
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
        'status' => 'active',
    ]);

    $application = AdmissionApplication::create([
        'tenant_id' => $institution->uuid,
        'uuid' => (string) Str::uuid(),
        'institution_id' => $institution->id,
        'academic_year_id' => $academicYear->id,
        'class_id' => $class->id,
        'application_number' => 'APP-TEST123',
        'application_date' => '2026-01-15',
        'student_name' => 'Test Student',
        'date_of_birth' => '2015-01-15',
        'gender' => 'male',
        'father_name' => 'Father Test',
        'mother_name' => 'Mother Test',
        'parent_phone' => '1234567890',
        'address' => '123 Test Street',
        'status' => 'submitted',
    ]);

    $response = get(route('admissions.success', [
        'institution' => $institution->uuid,
        'application' => $application->uuid,
    ]));

    expect($response->status())->toBe(200);
});
