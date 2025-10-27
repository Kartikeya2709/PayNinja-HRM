<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use App\Models\DemoRequest;
use App\Http\Controllers\Controller;

class DemoRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('throttle:3,1');
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'company_id' => 'nullable|integer|exists:companies,id',
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:20',
                'company_name' => 'required|string|max:255',
                'company_size' => 'nullable|string|max:100',
                'additional_info' => 'required|string',
            ]);

            $demoRequest = DemoRequest::create($validatedData);

            return response()->json([
                'message' => 'Demo request submitted successfully.',
                'data' => $demoRequest,
            ], 201);
        } catch (ThrottleRequestsException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Please wait for 1 minute before submitting another demo request.',
                'errors' => [
                        'throttle' => 'Rate limit exceeded'
                    ]
            ], 429);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while submitting the demo request.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}