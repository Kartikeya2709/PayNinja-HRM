<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;

class Handler extends Exception
{
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        // If it's an API request, return JSON response
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'Access Denied: Invalid or missing token.'
            ], 401);
        }

        // Otherwise, redirect to login for web routes
        return redirect()->guest(route('login'));
    }
}
