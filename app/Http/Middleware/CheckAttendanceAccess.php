<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AttendanceSetting;
use Symfony\Component\HttpFoundation\Response;

class CheckAttendanceAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply to authenticated users
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        $companyId = $user->company_id;

        // Get attendance settings for the company
        $settings = AttendanceSetting::where('company_id', $companyId)
            ->latest('updated_at')
            ->withoutGlobalScopes()
            ->first();

        if (!$settings) {
            // If no settings, allow access (fallback to both)
            return $next($request);
        }

        $checkinMethods = $settings->checkin_methods ?? 'both';
        $isApiRequest = $request->is('api/*');

        // Check access based on settings
        if ($isApiRequest && $checkinMethods === 'web') {
            // API access disabled when only web is allowed
            return response()->json([
                'success' => false,
                'message' => 'Attendance access through the mobile app is disabled. Please use the web interface.'
            ], 404);
        }

        if (!$isApiRequest && $checkinMethods === 'app') {
            // Web access disabled when only app is allowed
            abort(403 , 'Attendance web access is disabled. Please use the mobile app.');
        }

        return $next($request);
    }
}
