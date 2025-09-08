<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        // For superadmin, allow company_id in URL
        if (auth()->user()->role === 'superadmin') {
            return $next($request);
        }

        // Get company_id from authenticated user
        $companyId = auth()->user()->company_id;
        
        if (!$companyId) {
            abort(403, 'No company access');
        }

        // Add company_id to the request
        $request->merge(['company_id' => $companyId]);
        
        return $next($request);
    }
}
