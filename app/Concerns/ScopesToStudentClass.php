<?php

namespace App\Concerns;

use Illuminate\Support\Facades\Auth;

trait ScopesToStudentClass
{
    /**
     * Check if the current user is a student.
     */
    protected function isStudentUser(): bool
    {
        return Auth::user()?->role === 'student';
    }

    /**
     * Get the current student's class_id.
     */
    protected function studentClassId(): ?int
    {
        return Auth::user()?->student?->class_id;
    }

    /**
     * Get the current student's section_id.
     */
    protected function studentSectionId(): ?int
    {
        return Auth::user()?->student?->section_id;
    }

    /**
     * Get the current student's id.
     */
    protected function studentId(): ?int
    {
        return Auth::user()?->student?->id;
    }
}
