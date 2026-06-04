<?php

namespace App\Concerns;

use App\Models\Student;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

trait ScopesToParentChildren
{
    /**
     * Get all students linked to the current parent user.
     */
    protected function parentChildren(): Collection
    {
        $parentRecord = Auth::user()?->parent;

        if (! $parentRecord) {
            return new Collection();
        }

        return Student::whereHas('parents', fn ($q) => $q->where('parents.id', $parentRecord->id))
            ->with(['user', 'class', 'section'])
            ->get();
    }

    /**
     * Get an array of student IDs for the current parent.
     */
    protected function parentChildIds(): array
    {
        return $this->parentChildren()->pluck('id')->all();
    }
}
