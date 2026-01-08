<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class AnnouncementController extends Controller
{
    /**
     * Decrypt encrypted ID safely
     */
    private function getAnnouncementFromEncryptedId(string $encryptedId): Announcement
    {
        try {
            $id = Crypt::decrypt($encryptedId);
            return Announcement::findOrFail($id);
        } catch (\Exception $e) {
            abort(404);
        }
    }

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

        Announcement::create([
            'company_id' => $user->employee->company_id,
            'created_by' => $user->id,
            'title' => $request->title,
            'description' => $request->description,
            'audience' => $request->audience,
            'publish_date' => $request->publish_date,
            'expires_at' => $request->expires_at,
        ]);

        return redirect()->route('announcements.index')
            ->with('success', 'Announcement created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $announcement)
    {
        $announcement = $this->getAnnouncementFromEncryptedId($announcement);

        return view('company_admin.announcements.show', compact('announcement'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $announcement)
    {
        $announcement = $this->getAnnouncementFromEncryptedId($announcement);

        return view('company_admin.announcements.edit', compact('announcement'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $announcement)
    {
        $announcement = $this->getAnnouncementFromEncryptedId($announcement);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'audience' => 'required|in:employees,admins,both',
            'publish_date' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:publish_date',
        ]);

        $announcement->update([
            'title' => $request->title,
            'description' => $request->description,
            'audience' => $request->audience,
            'publish_date' => $request->publish_date,
            'expires_at' => $request->expires_at,
        ]);

        return redirect()->route('announcements.index')
            ->with('success', 'Announcement updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $announcement)
    {
        $announcement = $this->getAnnouncementFromEncryptedId($announcement);
        $announcement->delete();

        return redirect()->route('announcements.index')
            ->with('success', 'Announcement deleted successfully!');
    }
}
