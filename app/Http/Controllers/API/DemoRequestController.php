<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DemoRequest;

class DemoRequestController extends Controller

{
    public function store(Request $request)
    {
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
    }
}
