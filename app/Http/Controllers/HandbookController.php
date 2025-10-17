<?php

namespace App\Http\Controllers;

use App\Models\Handbook;
use App\Models\HandbookAcknowledgment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class HandbookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $companyId = $user->employee->company_id ?? null;

        if (!$companyId) {
            abort(403, 'No company associated with your account.');
        }

        $query = Handbook::with(['creator', 'department'])
            ->where('company_id', $companyId);

        if (!$user->hasRole(['admin', 'company_admin'])) {
            $query->published();

            // Filter by user's department if handbook is targeted
            $userDepartmentId = $user->employee->department_id ?? null;

            $query->where(function ($q) use ($userDepartmentId) {
                $q->whereNull('department_id')
                  ->orWhere('department_id', $userDepartmentId);
            });
        }

        $handbooks = $query->latest()->paginate(10);

        return view('handbooks.index', compact('handbooks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        $companyId = $user->employee->company_id ?? null;

        if (!$companyId) {
            abort(403, 'No company associated with your account.');
        }

        $departments = \App\Models\Department::where('company_id', $companyId)->get();

        return view('handbooks.create', compact('departments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            // 'file' => 'required|file|mimes:pdf,doc,docx|max:10240', // 10MB max
            'version' => 'nullable|string|max:50',
            'status' => 'required|in:draft,published',
        ]);

        if ($request->hasFile('file')) {
             $filePath = $request->file('file')->store('handbooks', 'public');

        }
       
        $user = Auth::user();
        $companyId = $user->employee->company_id ?? null;

        if (!$companyId) {
            abort(403, 'No company associated with your account.');
        }

        Handbook::create([
            'title' => $request->title,
            'description' => $request->description,
            'file_path' => $filePath ?? 'NULL',
            'version' => $request->version ?? 'v1.0',
            'status' => $request->status,
            'created_by' => Auth::id(),
            'company_id' => $companyId,
            'department_id' => $request->department_id ?: null,
        ]);

        return redirect()->route('handbooks.index')->with('success', 'Handbook created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Handbook $handbook)
    {
        $user = Auth::user();
        $companyId = $user->employee->company_id ?? null;

        if (!$companyId || $handbook->company_id !== $companyId) {
            abort(403, 'You do not have access to this handbook.');
        }

        if (!$user->hasRole(['admin', 'company_admin']) && $handbook->status !== 'published') {
            abort(403);
        }

        $acknowledged = $handbook->isAcknowledgedBy($user);

        return view('handbooks.show', compact('handbook', 'acknowledged'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Handbook $handbook)
    {
        $user = Auth::user();
        $companyId = $user->employee->company_id ?? null;

        if (!$companyId || $handbook->company_id !== $companyId) {
            abort(403, 'You do not have access to this handbook.');
        }

        $departments = \App\Models\Department::where('company_id', $companyId)->get();

        return view('handbooks.edit', compact('handbook', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Handbook $handbook)
    {
        // dd($request->all());
        $user = Auth::user();
        $companyId = $user->employee->company_id ?? null;

        if (!$companyId || $handbook->company_id !== $companyId) {
            abort(403, 'You do not have access to this handbook.');
        }

        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            // 'file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'version' => 'nullable|string|max:50',
            'department_id' => 'nullable|exists:departments,id',
        ];

        // Only allow status change if handbook is in draft status
        if ($handbook->status === 'draft') {
            $rules['status'] = 'required|in:draft,published';
        }

        $request->validate($rules);

        $data = [
            'title' => $request->title,
            'description' => $request->description,
            'version' => $request->version ?? $handbook->version,
            'department_id' => $request->department_id ?: null,
        ];

        // Only update status if handbook is in draft and status is provided
        if ($handbook->status === 'draft' && $request->has('status')) {
            $data['status'] = $request->status;
        }

        if ($request->hasFile('file')) {
            // Delete old file
            Storage::disk('public')->delete($handbook->file_path);
            $data['file_path'] = $request->file('file')->store('handbooks', 'public');
        }

        $handbook->update($data);

        return redirect()->route('handbooks.index')->with('success', 'Handbook updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Handbook $handbook)
    {
        $user = Auth::user();
        $companyId = $user->employee->company_id ?? null;

        if (!$companyId || $handbook->company_id !== $companyId) {
            abort(403, 'You do not have access to this handbook.');
        }

        Storage::disk('public')->delete($handbook->file_path);
        $handbook->delete();

        return redirect()->route('handbooks.index')->with('success', 'Handbook deleted successfully.');
    }

    /**
     * Acknowledge the handbook.
     */
    public function acknowledge(Handbook $handbook)
    {
        $user = Auth::user();
        $companyId = $user->employee->company_id ?? null;

        if (!$companyId || $handbook->company_id !== $companyId) {
            abort(403, 'You do not have access to this handbook.');
        }

        if ($handbook->status !== 'published') {
            abort(403,'Publish the handbook before acknowledgment.');
        }

        HandbookAcknowledgment::updateOrCreate(
            [
                'handbook_id' => $handbook->id,
                'user_id' => Auth::id(),
            ],
            [
                'acknowledged_at' => now(),
            ]
        );

        return redirect()->back()->with('success', 'Handbook acknowledged successfully.');
    }

}
