<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ContactMessage;

class ContactMessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('throttle:10,1');
    }
    public function store(Request $request)
    {
        try {

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'additional_info' => 'nullable|string',
        ]);

        $contactMessage = ContactMessage::create($validatedData);

        return response()->json([
            'message' => 'Contact message submitted successfully.',
            'data' => $contactMessage,
        ], 201);
    }

    catch(ThrottleRequestsException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Too Many Attempts.',
            'errors' => ['Too many attempts. Please try again in X seconds.']
        ], 429);
    }
    
    catch (\Exception $e) {
        return response()->json([
            'message' => 'An error occurred while submitting the contact message.',
            'error' => $e->getMessage(),
        ], 500);
    }
    }

}
