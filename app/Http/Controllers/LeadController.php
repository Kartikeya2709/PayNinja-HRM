<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeadController extends Controller
{
    public function __construct()
    {
        $this->middleware(['role:admin,company_admin']);
    }

    /**
     * Display a listing of the leads.
     */
    public function index()
    {
        $user = Auth::user();
        $companyId = $user->company_id;

        $leads = Lead::where('company_id', $companyId)->orderBy('created_at', 'desc')->paginate(10);

        return view('company-admin.leads.index', compact('leads'));
    }

    /**
     * Show the form for creating a new lead.
     */
    public function create()
    {
        return view('company-admin.leads.create');
    }

    /**
     * Store a newly created lead in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $companyId = $user->company_id;

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'status' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        Lead::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'company_id' => $companyId,
            'status' => $request->status,
            'source' => $request->source,
            'notes' => $request->notes,
        ]);

        return redirect()->route('company-admin.leads.index')
                        ->with('success', 'Lead created successfully.');
    }

    /**
     * Display the specified lead.
     */
    public function show(Lead $lead)
    {
        $user = Auth::user();
        if (!$user->hasRole('company_admin') && !$user->hasRole('admin')) {
            abort(403);
        }

        // Ensure the lead belongs to the user's company
        if ($lead->company_id !== $user->company_id) {
            abort(403);
        }

        return view('company-admin.leads.show', compact('lead'));
    }

    /**
     * Show the form for editing the specified lead.
     */
    public function edit(Lead $lead)
    {
        $user = Auth::user();
        if (!$user->hasRole('company_admin') && !$user->hasRole('admin')) {
            abort(403);
        }

        // Ensure the lead belongs to the user's company
        if ($lead->company_id !== $user->company_id) {
            abort(403);
        }

        return view('company-admin.leads.edit', compact('lead'));
    }

    /**
     * Update the specified lead in storage.
     */
    public function update(Request $request, Lead $lead)
    {
        $user = Auth::user();
        if (!$user->hasRole('company_admin') && !$user->hasRole('admin')) {
            abort(403);
        }

        // Ensure the lead belongs to the user's company
        if ($lead->company_id !== $user->company_id) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'status' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $lead->update($request->only([
            'name', 'email', 'phone', 'status', 'source', 'notes'
        ]));

        return redirect()->route('company-admin.leads.show', $lead)->with('success', 'Lead updated successfully.');
    }

    /**
     * Remove the specified lead from storage.
     */
    public function destroy(Lead $lead)
    {
        $user = Auth::user();

        // Only company_admin can delete leads, admin cannot
        if (!$user->hasRole('company_admin')) {
            abort(403, 'Only company admin can delete leads.');
        }

        // Ensure the lead belongs to the user's company
        if ($lead->company_id !== $user->company_id) {
            abort(403);
        }

        $lead->delete();

        return redirect()->route('leads.index')->with('success', 'Lead deleted successfully.');
    }
}
