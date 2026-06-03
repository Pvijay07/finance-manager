<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckCompanyAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $companyId = $request->route('company') ?? $request->input('company_id');

        if ($companyId && !$user->canAccessCompany($companyId)) {
            abort(403, 'You do not have access to this company.');
        }

        return $next($request);
    }
}
