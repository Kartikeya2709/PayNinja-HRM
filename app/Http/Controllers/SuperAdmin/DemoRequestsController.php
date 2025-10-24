<?php

namespace App\Http\Controllers\SuperAdmin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\DemoRequest;
use Illuminate\Support\Facades\DB;

class DemoRequestsController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10); // Default 10 items per page
        $demoRequests = DemoRequest::orderBy('created_at', 'desc')
            ->paginate($perPage);
        return view('superadmin.demo_requests', compact('demoRequests'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'company_id' => 'nullable|integer|exists:companies,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'company_name' => 'required|string|max:255',
            'company_size' => 'nullable|string|max:100',
            'additional_info' => 'required|string',
        ]);

        $demoRequest = DemoRequest::create($validatedData);

        return view('SuperAdmin.demo_requests' , compact('demoRequest'));
    }
}
