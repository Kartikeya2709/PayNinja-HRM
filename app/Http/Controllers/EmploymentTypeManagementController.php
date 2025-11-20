<?php

namespace App\Http\Controllers;

use App\Models\EmploymentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class EmploymentTypeManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin,company_admin']);
    }

    /**
     * Display a listing of employment types.
     */
    public function index()
    {
        $employmentTypes = EmploymentType::forCompany(auth()->user()->company_id)
            ->orderBy('name')
            ->get();

        return view('company.employment-types.index', compact('employmentTypes'));
    }

    /**
     * Show the form for creating a new employment type.
     */
    public function create()
    {
        return view('company.employment-types.create');
    }

    /**
     * Store a newly created employment type in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('employment_types')->where(function ($query) {
                    return $query->where('company_id', auth()->user()->company_id);
                }),
            ],
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        EmploymentType::create([
            'company_id' => auth()->user()->company_id,
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()->route('employment-types.index')
            ->with('success', 'Employment type created successfully.');
    }

    /**
     * Show the form for editing the employment type.
     */
    public function edit(EmploymentType $employmentType)
    {
        if ($employmentType->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        return view('company.employment-types.edit', compact('employmentType'));
    }

    /**
     * Update the specified employment type in storage.
     */
    public function update(Request $request, EmploymentType $employmentType)
    {
        if ($employmentType->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('employment_types')->where(function ($query) {
                    return $query->where('company_id', auth()->user()->company_id);
                })->ignore($employmentType->id),
            ],
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        $employmentType->update([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->is_active ?? $employmentType->is_active,
        ]);

        return redirect()->route('employment-types.index')
            ->with('success', 'Employment type updated successfully.');
    }
}
