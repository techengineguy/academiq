<?php

namespace App\Concerns;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait ProfileValidationRules
{
    /**
     * Get the validation rules used to validate user profiles.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function profileRules(?int $userId = null): array
    {
        return [
            'first_name' => $this->firstNameRules(),
            'last_name' => $this->lastNameRules(),
            'username' => $this->usernameRules($userId),
            'email' => $this->emailRules($userId),
        ];
    }

    /**
     * Get the validation rules used to validate institution registration.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function institutionRules(): array
    {
        return [
            'institution_name' => ['required', 'string', 'max:255'],
            'institution_code' => ['required', 'string', 'max:50', Rule::unique('institutions', 'code')],
            'institution_email' => ['nullable', 'string', 'email', 'max:255'],
            'institution_phone' => ['nullable', 'string', 'max:20'],
            'institution_address' => ['nullable', 'string', 'max:500'],
            'institution_city' => ['nullable', 'string', 'max:100'],
            'institution_state' => ['nullable', 'string', 'max:100'],
            'institution_country' => ['nullable', 'string', 'max:100'],
            'institution_postal_code' => ['nullable', 'string', 'max:20'],
        ];
    }

    /**
     * Get the validation rules used to validate user first names.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function firstNameRules(): array
    {
        return ['required', 'string', 'max:255'];
    }

    /**
     * Get the validation rules used to validate user last names.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function lastNameRules(): array
    {
        return ['required', 'string', 'max:255'];
    }

    /**
     * Get the validation rules used to validate user usernames.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function usernameRules(?int $userId = null): array
    {
        return [
            'required',
            'string',
            'max:255',
            'regex:/^[a-zA-Z0-9_-]+$/',
            $userId === null
                ? Rule::unique(User::class)
                : Rule::unique(User::class)->ignore($userId),
        ];
    }

    /**
     * Get the validation rules used to validate user names.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function nameRules(): array
    {
        return ['required', 'string', 'max:255'];
    }

    /**
     * Get the validation rules used to validate user emails.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function emailRules(?int $userId = null): array
    {
        return [
            'required',
            'string',
            'email',
            'max:255',
            $userId === null
                ? Rule::unique(User::class)
                : Rule::unique(User::class)->ignore($userId),
        ];
    }
}
