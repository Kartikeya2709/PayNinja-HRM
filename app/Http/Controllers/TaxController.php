<?php

namespace App\Http\Controllers;

use App\Models\Tax;
use App\Http\Requests\StoreTaxRequest;
use App\Http\Requests\UpdateTaxRequest;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TaxController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('superadmin');
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Tax::class);

        $query = Tax::query();

        // Filtering
        if ($request->has('search') && !empty($request->search)) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('country', 'like', '%' . $request->search . '%')
                  ->orWhere('state', 'like', '%' . $request->search . '%');
        }

        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('country') && !empty($request->country)) {
            $query->where('country', $request->country);
        }

        $taxes = $query->paginate(15);

        return view('superadmin.taxes.index', compact('taxes'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Tax::class);

        $request->validate([
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0|max:100',
            'country' => 'nullable|string|size:2',
            'state' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $tax = Tax::create($request->only([
                'name', 'rate', 'country', 'state', 'is_active'
            ]));

            // Log audit
            AuditLogService::logCreated($tax, 'Tax created successfully');

            DB::commit();
            return redirect()->route('superadmin.taxes.index')
                ->with('success', 'Tax created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create tax', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            return back()->withInput()->with('error', 'Failed to create tax');
        }
    }

    public function update(Request $request, $id)
    {
        $tax = Tax::findOrFail($id);
        $this->authorize('update', $tax);

        $request->validate([
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0|max:100',
            'country' => 'nullable|string|size:2',
            'state' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $oldValues = $tax->toArray();

        DB::beginTransaction();
        try {
            $tax->update($request->only([
                'name', 'rate', 'country', 'state', 'is_active'
            ]));

            // Log audit
            AuditLogService::logUpdated($tax, $oldValues, $tax->toArray(), 'Tax updated successfully');

            DB::commit();
            return redirect()->route('superadmin.taxes.index')
                ->with('success', 'Tax updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update tax', [
                'error' => $e->getMessage(),
                'tax_id' => $id,
                'user_id' => auth()->id()
            ]);
            return back()->withInput()->with('error', 'Failed to update tax');
        }
    }

    public function destroy($id)
    {
        $tax = Tax::findOrFail($id);
        $this->authorize('delete', $tax);

        // Check if tax is used in any invoices
        if ($tax->invoices()->exists()) {
            return back()->with('error', 'Cannot delete tax that is used in invoices');
        }

        DB::beginTransaction();
        try {
            $tax->delete();

            // Log audit
            AuditLogService::logDeleted($tax, 'Tax deleted successfully');

            DB::commit();
            return redirect()->route('superadmin.taxes.index')
                ->with('success', 'Tax deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete tax', [
                'error' => $e->getMessage(),
                'tax_id' => $id,
                'user_id' => auth()->id()
            ]);
            return back()->with('error', 'Failed to delete tax');
        }
    }
}