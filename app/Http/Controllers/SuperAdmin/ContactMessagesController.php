<?php

namespace App\Http\Controllers\SuperAdmin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ContactMessage;

class ContactMessagesController extends Controller
{
    public function index()
    {
        $contactMessages = ContactMessage::orderBy('created_at', 'desc')->get();
        return view('superadmin.contact_messages', compact('contactMessages'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'additional_info' => 'required|string',
        ]);

        $contactMessage = ContactMessage::create($validatedData);

        return view();
    }
}
