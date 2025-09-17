<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $companyId = $user->employee->company_id ?? null;

        $announcements = Announcement::where('company_id', $companyId)
            ->whereIn('audience', ['employees', 'admins', 'both'])
            ->latest()
            ->get();

        return view('company_admin.announcements.index', compact('announcements'));
    }
 
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('company_admin.announcements.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'audience' => 'required|in:employees,admins,both',
            'publish_date' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:publish_date',
        ]);

        $user = Auth::user();
        $audience = $request->audience;

        // HR Admins can only target employees
        if ($user->hasRole('admin')) {
            $audience = 'employees';
        }

        Announcement::create([
            'company_id' => $user->employee->company_id,
            'created_by' => $user->id,
            'title' => $request->title,
            'description' => $request->description,
            'audience' => $audience,
            'publish_date' => $request->publish_date,
            'expires_at' => $request->expires_at,
        ]);

        return redirect()->route('company-admin.announcements.index')
            ->with('success', 'Announcement created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Announcement $announcement)
    {
        $user = Auth::user();

        // Ensure only same-company users can view
        if ($announcement->company_id !== $user->employee->company_id) {
            abort(403, 'Unauthorized access.');
        }

        return view('company_admin.announcements.show', compact('announcement'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Announcement $announcement)
    {
        $user = Auth::user();

        // Only company_admin who created it can edit
        if ($announcement->created_by !== $user->id && !$user->hasRole('company_admin')) {
            abort(403, 'Unauthorized access.');
        }

        return view('company_admin.announcements.edit', compact('announcement'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Announcement $announcement)
    {
        $user = Auth::user();

        // Only company_admin who created it can update
        if ($announcement->created_by !== $user->id && !$user->hasRole('company_admin')) {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'audience' => 'required|in:employees,admins,both',
            'publish_date' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:publish_date',
        ]);

        $audience = $request->audience;

        // HR Admins can only target employees
        if ($user->hasRole('admin')) {
            $audience = 'employees';
        }

        $announcement->update([
            'title' => $request->title,
            'description' => $request->description,
            'audience' => $audience,
            'publish_date' => $request->publish_date,
            'expires_at' => $request->expires_at,
        ]);

        return redirect()->route('company-admin.announcements.index')
            ->with('success', 'Announcement updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Announcement $announcement)
    {
        $user = Auth::user();

        // Only company_admin who created it can delete
        if ($announcement->created_by !== $user->id && !$user->hasRole('company_admin')) {
            abort(403, 'Unauthorized access.');
        }

        $announcement->delete();

        return redirect()->route('company-admin.announcements.index')
            ->with('success', 'Announcement deleted (soft deleted) successfully!');
    }
}
