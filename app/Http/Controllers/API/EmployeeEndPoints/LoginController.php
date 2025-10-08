<?php

namespace App\Http\Controllers\API\EmployeeEndPoints;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\SuperAdmin;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('api');
    }
    /**
     * Handle an API login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        try {
            // Validate the request data
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ]);

            Log::info('API Login attempt for email: ' . $request->input('email'));

            $credentials = $request->only('email', 'password');
            $email = $request->email;

            // Check if user exists
            $user = User::where('email', $email)->first();
            $superadmin = SuperAdmin::where('email', $email)->first();

            if (!$user && !$superadmin) {
                Log::warning('API Login failed: email not found for ' . $email);
                return response()->json([
                    'status' => false,
                    'message' => 'This email is not registered.',
                ], 401);
            }

            // Try superadmin authentication first
            if (Auth::guard('superadmin')->attempt($credentials)) {
                $user = Auth::guard('superadmin')->user();
                $token = $user->createToken('SuperAdminToken')->plainTextToken;

                Log::info('API Login successful as SuperAdmin for user: ' . $user->email);

                return response()->json([
                    'status' => true,
                    'message' => 'Login successful',
                    'user' => [
                        'id' => $user->id,
                        'email' => $user->email,
                        'role' => 'superadmin'
                    ],
                    'token' => $token,
                ]);
            }

            // Try user authentication
            if (Auth::guard('web')->attempt($credentials)) {
                $user = Auth::guard('web')->user();
                $token = $user->createToken('UserToken')->plainTextToken;

                Log::info('API Login successful as User for: ' . $user->email . ' with role: ' . $user->role);

                // Get additional user information if needed
                $userInfo = [];
                if ($user->role === 'user') {
                    $employee = $user->employee;
                    if ($employee) {
                        $userInfo = [
                            'employee_id' => $employee->id,
                            'name' => $employee->first_name . ' ' . $employee->last_name,
                            'department' => $employee->department ? $employee->department->name : null,
                            'designation' => $employee->designation ? $employee->designation->name : null,
                        ];
                    }
                }

                return response()->json([
                    'status' => true,
                    'message' => 'Login successful',
                    'user' => array_merge([
                        'id' => $user->id,
                        'email' => $user->email,
                        'role' => $user->role,
                    ], $userInfo),
                    'token' => $token,
                ]);
            }

            // If both authentication attempts fail
            Log::warning('API Login failed: incorrect password for ' . $email);
            return response()->json([
                'status' => false,
                'message' => 'Invalid credentials',
            ], 401);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('API Login error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'An error occurred during login',
            ], 500);
        }
    }

    /**
     * Handle API logout request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            // Revoke all tokens for the current user
            $request->user()->tokens()->delete();

            return response()->json([
                'status' => true,
                'message' => 'Successfully logged out'
            ]);
        } catch (\Exception $e) {
            Log::error('API Logout error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'An error occurred during logout'
            ], 500);
        }
    }

    /**
     * Get the authenticated user's information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function user(Request $request)
    {
        try {
            $user = $request->user();
            $userInfo = [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role
            ];

            if ($user->role === 'user' && $user->employee) {
                $employee = $user->employee;
                $userInfo['employee'] = [
                    'id' => $employee->id,
                    'first_name' => $employee->first_name,
                    'last_name' => $employee->last_name,
                    'department' => $employee->department ? $employee->department->name : null,
                    'designation' => $employee->designation ? $employee->designation->name : null,
                ];
            }

            return response()->json([
                'status' => true,
                'user' => $userInfo
            ]);
        } catch (\Exception $e) {
            Log::error('API User info error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching user information'
            ], 500);
        }
    }
}
