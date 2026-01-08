<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetAssignment;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class AssetController extends Controller
{
    /**
     * Get asset from encrypted ID.
     */
    private function getAssetFromEncryptedId(string $encryptedId): Asset
    {
        try {
            $id = Crypt::decrypt($encryptedId);
            return Asset::where('company_id', Auth::user()->company_id)->findOrFail($id);
        } catch (\Exception $e) {
            abort(404);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $assets = Asset::where('company_id', Auth::user()->company_id)
            ->with(['category', 'currentAssignment.employee'])
            ->latest()
            ->paginate(10);

            // dd($assets);

        return view('assets.index', compact('assets'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = AssetCategory::where('company_id', Auth::user()->company_id)
            ->pluck('name', 'id');

        return view('assets.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:asset_categories,id',
            'purchase_cost' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'condition' => 'required|in:good,fair,poor,damaged',
            'notes' => 'nullable|string'
        ]);

        $validated['company_id'] = Auth::user()->company_id;
        $validated['asset_code'] = 'AST-' . strtoupper(Str::random(8));
        $validated['status'] = 'available';

        Asset::create($validated);

        return redirect()->route('assets.index')
            ->with('success', 'Asset created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $encryptedId)
    {
        $id = Crypt::decrypt($encryptedId);
        $asset = Asset::where('company_id', Auth::user()->company_id)
            ->with(['category', 'assignments.employee', 'assignments.assignedBy', 'conditions'])
            ->findOrFail($id);
        // $asset = $this->getAssetFromEncryptedId($encryptedId)
        //     ->with(['category', 'assignments.employee', 'assignments.assignedBy', 'conditions']);

        return view('assets.show', compact('asset'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $encryptedId)
    {
        $asset = $this->getAssetFromEncryptedId($encryptedId);

        $categories = AssetCategory::where('company_id', Auth::user()->company_id)
            ->pluck('name', 'id');

        return view('assets.create', compact('asset', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $encryptedId)
    {
        $asset = $this->getAssetFromEncryptedId($encryptedId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:asset_categories,id',
            'purchase_cost' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'condition' => 'required|in:good,fair,poor,damaged',
            'notes' => 'nullable|string'
        ]);

        $asset->update($validated);

        return redirect()->route('assets.index')
            ->with('success', 'Asset updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $encryptedId)
    {
        $asset = $this->getAssetFromEncryptedId($encryptedId);

        // Check if asset is currently assigned
        if ($asset->status === 'assigned') {
            return redirect()->route('assets.index')
                ->with('error', 'Cannot delete an asset that is currently assigned.');
        }

        $asset->delete();

        return redirect()->route('assets.index')
            ->with('success', 'Asset deleted successfully.');
    }

    /**
     * Display the employee's assigned assets.
     */
    public function ownAssets()
    {
        $employee = Auth::user()->employee;

        $assignments = AssetAssignment::where('employee_id', $employee->id)
            ->with(['asset.category'])
            ->orderBy('assigned_date', 'desc')
            ->get();

        return view('assets.own-assets', compact('assignments'));
    }

    /**
     * Display asset dashboard.
     */
    public function dashboard()
    {
        $user = Auth::user();
        $companyId = $user->company_id;

        // Total assets count
        $totalAssets = Asset::where('company_id', $companyId)->count();

        // Total value of all assets
        $totalValue = Asset::where('company_id', $companyId)->sum('purchase_cost');

        // Available assets count
        $availableAssets = Asset::where('company_id', $companyId)->where('status', 'available')->count();

        // Assigned assets count
        $assignedAssets = Asset::where('company_id', $companyId)->where('status', 'assigned')->count();

        // Asset utilization rate
        $utilizationRate = $totalAssets > 0 ? round(($assignedAssets / $totalAssets) * 100, 1) : 0;

        // Assets by condition
        $assetsByCondition = Asset::where('company_id', $companyId)
            ->get()
            ->groupBy('condition')
            ->map(function ($group) {
                return $group->count();
            })
            ->toArray();

        // Assets by category
        $assetsByCategory = Asset::where('company_id', $companyId)
            ->with('category')
            ->get()
            ->groupBy(function ($asset) {
                return $asset->category->name ?? 'Uncategorized';
            })
            ->map(function ($group, $categoryName) {
                return [
                    'category' => $categoryName,
                    'count' => $group->count()
                ];
            })
            ->sortByDesc('count')
            ->values();

        // Average asset age (in months)
        $averageAge = Asset::where('company_id', $companyId)
            ->whereNotNull('purchase_date')
            ->selectRaw('AVG(TIMESTAMPDIFF(MONTH, purchase_date, CURDATE())) as avg_age')
            ->first()
            ->avg_age;
        $averageAge = $averageAge ? round($averageAge, 1) : 0;

        // Overdue assignments (expected return date passed)
        $overdueAssignments = AssetAssignment::whereHas('asset', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
            ->whereNull('returned_date')
            ->where('expected_return_date', '<', now())
            ->count();

        // Recent assignments this month
        $assignmentsThisMonth = AssetAssignment::whereHas('asset', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
            ->whereMonth('assigned_date', now()->month)
            ->whereYear('assigned_date', now()->year)
            ->count();

        // Most assigned employees (top 5)
        $mostAssignedEmployees = Employee::where('company_id', $companyId)
            ->withCount(['assignments' => function ($query) {
                $query->whereNull('returned_date');
            }])
            ->having('assignments_count', '>', 0)
            ->orderBy('assignments_count', 'desc')
            ->limit(5)
            ->get();

        // Department-wise asset distribution
        $departmentAssets = Employee::where('company_id', $companyId)
            ->with(['department', 'assignments' => function ($query) {
                $query->whereNull('returned_date');
            }])
            ->get()
            ->groupBy('department.name')
            ->map(function ($employees, $deptName) {
                $totalAssets = $employees->sum(function ($employee) {
                    return $employee->assignments->count();
                });
                return [
                    'department' => $deptName ?? 'No Department',
                    'assets' => $totalAssets
                ];
            })
            ->filter(function ($item) {
                return $item['assets'] > 0;
            })
            ->sortByDesc('assets')
            ->take(5);

        // Assets inventory with details (limited to 5)
        $assets = Asset::where('company_id', $companyId)
            ->with(['category', 'currentAssignment.employee'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Employees with their assigned assets (limited to 5)
        $employeesWithAssets = Employee::where('company_id', $companyId)
            ->with(['assignments.asset'])
            ->whereHas('assignments', function ($query) {
                $query->whereNull('returned_date');
            })
            ->limit(5)
            ->get();

        // Recent assignments (limited to 5)
        $recentAssignments = AssetAssignment::with(['asset', 'employee'])
            ->whereHas('asset', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
            ->orderBy('assigned_date', 'desc')
            ->limit(5)
            ->get();

        return view('assets.dashboard', compact(
            'totalAssets',
            'totalValue',
            'availableAssets',
            'assignedAssets',
            'utilizationRate',
            'assetsByCondition',
            'assetsByCategory',
            'averageAge',
            'overdueAssignments',
            'assignmentsThisMonth',
            'mostAssignedEmployees',
            'departmentAssets',
            'assets',
            'employeesWithAssets',
            'recentAssignments'
        ));
    }

    /**
     * Display employees with assigned assets.
     */
    public function employeesWithAssets()
    {
        $user = Auth::user();
        $companyId = $user->company_id;

        // Employees with their assigned assets
        $employees = Employee::where('company_id', $companyId)
            ->with(['assignments.asset'])
            ->whereHas('assignments', function ($query) {
                $query->whereNull('returned_date');
            })
            ->paginate(15);

        return view('assets.employees', compact('employees'));
    }

}
