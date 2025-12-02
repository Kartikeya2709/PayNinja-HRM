<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Route;

class checkAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        \Log::info("RoleMiddleware triggered for request to: " . $request->path());
        $slug = Route::getRoutes()->match($request)->uri;
        if(!$request->ajax() && !User::hasAccess($slug, true)) {
           return abort(403);
        }
        return $next($request);
    }
}
