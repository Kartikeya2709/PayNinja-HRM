<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ContactMessage;

class ContactMessageController extends Controller
{
    public function store(Request $request)
    {
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

}
