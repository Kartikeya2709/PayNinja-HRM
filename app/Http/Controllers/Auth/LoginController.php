<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // Import the Log facade

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Handle a login request to the application.
     */
    public function login(Request $request)
    {
        // ✅ **Validate the request data**
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        Log::info('Login attempt for email: ' . $request->input('email'));
        // Log::info('Login attempt Request: ', $request->all());

        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember') ? true : false;
        Log::info('Remember: '. $remember);

        // Attempt login with superadmin guard first
        if (Auth::guard('superadmin')->attempt($credentials, $remember)) {
            $user = Auth::guard('superadmin')->user();
            Log::info('Login successful as SuperAdmin for user: ' . $user->email);
            Log::info('Redirecting SuperAdmin to home');
            return redirect()->route('home'); 
        }

        // If superadmin login fails, attempt with default web guard (users)
        if (Auth::guard('web')->attempt($credentials, $remember)) {
            $user = Auth::guard('web')->user();
            Log::info('Login successful as User/Admin for user: ' . $user->email . ' with role: ' . $user->role);

            switch ($user->role) {
                case 'admin':
                    Log::info('Redirecting Admin to home');
                    return redirect()->route('home');
                case 'user':
                    Log::info('Redirecting User to home');
                    return redirect()->route('home');
                default:
                    Log::info('Unknown role for user: ' . $user->email . ', redirecting to home');
                    return redirect()->route('home');
            }
        }

        // If both attempts fail
        Log::warning('Login failed for email: ' . $request->input('email'));
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Handle unauthorized access and logout.
     */
    protected function unauthorizedAccess()
    {
        // Log unauthorized access
        Log::warning('Unauthorized access attempt. Logging out.');
        
        Auth::logout();
        return redirect()->route('login')->with('error', 'Unauthorized Access');
    }
}
