<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $companyId = $request->user()?->company_id;

        if (! $companyId) {
            abort(403, 'Tenant context not found for the authenticated user.');
        }

        app()->instance('currentCompanyId', $companyId);

        return $next($request);
    }
}
