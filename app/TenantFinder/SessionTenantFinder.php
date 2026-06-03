<?php

namespace App\TenantFinder;

use App\Models\Institution;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\TenantFinder\TenantFinder;

class SessionTenantFinder extends TenantFinder
{
    public function findForRequest(Request $request): ?IsTenant
    {
        // Guard: session middleware has not run yet (e.g. early boot or CLI).
        if (! $request->hasSession()) {
            return null;
        }

        // 1. Check session for an explicitly selected institution.
        $institutionId = $request->session()->get('active_institution_id');

        if (! $institutionId) {
            /** @var User|null $user */
            $user = Auth::guard('web')->user();

            if ($user) {
                // 2a. Try the user's direct institution_id column.
                if ($user->institution_id) {
                    $institutionId = $user->institution_id;
                } else {
                    // 2b. Fall back to the first admin institution (pivot table).
                    $first = $user->adminInstitutions()->first();

                    if ($first) {
                        $institutionId = $first->id;
                    }
                }

                if ($institutionId) {
                    $request->session()->put('active_institution_id', $institutionId);
                }
            }
        }

        if (! $institutionId) {
            return null;
        }

        return Institution::find($institutionId);
    }
}
