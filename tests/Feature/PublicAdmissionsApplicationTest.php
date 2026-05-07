<?php

use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\Institution;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

it('allows guests to access the tenant public admissions application page', function () {
    $institution = Institution::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'North Ridge Academy',
        'code' => 'NRA',
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

    $response = get(route('admissions.apply', ['institution' => $institution->uuid]));

    $response->assertOk();
    $response->assertSee('North Ridge Academy');
    $response->assertSee('2026-2027');
    $response->assertSee('Grade 1');
    expect(Auth::check())->toBeFalse();
});
