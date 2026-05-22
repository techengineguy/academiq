<?php

namespace App\Observers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;

class UserObserver
{
    public function created(User $user): void
    {
        $this->assignDefaultRole($user);
    }

    public function updated(User $user): void
    {
        // If the role field changed, reassign the matching role
        if ($user->wasChanged('role')) {
            $this->assignDefaultRole($user);
        }
    }

    private function assignDefaultRole(User $user): void
    {
        if (! $user->tenant_id || ! $user->role) {
            return;
        }

        $roleSlug = $this->mapUserRoleToSlug($user->role);

        if (! $roleSlug) {
            return;
        }

        $role = Role::where('tenant_id', $user->tenant_id)
            ->where('slug', $roleSlug)
            ->first();

        if (! $role) {
            return;
        }

        // Only attach if not already assigned
        if (! $user->roles()->where('roles.id', $role->id)->exists()) {
            $user->roles()->attach($role->id, [
                'tenant_id' => $user->tenant_id,
                'uuid' => Str::uuid(),
            ]);
        }
    }

    private function mapUserRoleToSlug(string $userRole): ?string
    {
        return match ($userRole) {
            'admin' => 'administrator',
            'teacher' => 'teacher',
            'staff' => 'staff',
            'accountant' => 'accountant',
            'student' => 'student',
            'parent' => 'parent',
            default => null,
        };
    }
}
