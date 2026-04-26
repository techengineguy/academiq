<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user with institution.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->institutionRules(),
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        // Create Institution
        $institution = Institution::create([
            'uuid' => Str::uuid(),
            'name' => $input['institution_name'],
            'code' => $input['institution_code'],
            'email' => $input['institution_email'] ?? null,
            'phone' => $input['institution_phone'] ?? null,
            'address' => $input['institution_address'] ?? null,
            'city' => $input['institution_city'] ?? null,
            'state' => $input['institution_state'] ?? null,
            'country' => $input['institution_country'] ?? null,
            'postal_code' => $input['institution_postal_code'] ?? null,
            'status' => 'active',
        ]);

        // Create User (Admin for this institution)
        return User::create([
            'uuid' => Str::uuid(),
            'institution_id' => $institution->id,
            'username' => $input['username'],
            'email' => $input['email'],
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'password' => $input['password'],
            'role' => 'admin',
            'is_active' => true,
        ]);
    }
}
