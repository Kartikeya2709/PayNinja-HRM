<?php

namespace App\Http\Controllers;

use App\Models\CompanyPackage;
use App\Models\CompanyPackageInvoice;
use App\Models\Discount;
use App\Models\Tax;
use App\Http\Requests\GenerateInvoiceRequest;
use App\Services\AuditLogService;
use App\Services\BillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    protected BillingService $billingService;

    public function __construct(BillingService $billingService)
    {
        $this->middleware('auth');
        $this->middleware('superadmin');
        $this->billingService = $billingService;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', CompanyPackageInvoice::class);

        $query = CompanyPackageInvoice::with(['companyPackage.company', 'companyPackage.package', 'discount', 'tax']);

        // Filtering
        if ($request->has('company_id') && !empty($request->company_id)) {
            $query->whereHas('companyPackage', function ($q) use ($request) {
                $q->where('company_id', $request->company_id);
            });
        }

        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        if ($request->has('billing_period_start') && !empty($request->billing_period_start)) {
            $query->where('billing_period_start', '>=', $request->billing_period_start);
        }

        if ($request->has('billing_period_end') && !empty($request->billing_period_end)) {
            $query->where('billing_period_end', '<=', $request->billing_period_end);
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('superadmin.invoices.index', compact('invoices'));
    }

    public function generate(GenerateInvoiceRequest $request)
    {
        $this->authorize('create', CompanyPackageInvoice::class);

        DB::beginTransaction();
        try {
            $companyPackage = CompanyPackage::with(['company', 'package.pricingTiers'])
                ->findOrFail($request->company_package_id);

            // Determine billing period
            $billingPeriodStart = $request->billing_period_start ? Carbon::parse($request->billing_period_start) : now()->startOfMonth();
            $billingPeriodEnd = $request->billing_period_end ? Carbon::parse($request->billing_period_end) : now()->endOfMonth();

            // Use BillingService to generate invoice
            $invoice = $this->billingService->generateInvoice($companyPackage, $billingPeriodStart, $billingPeriodEnd);

            // Log audit
            AuditLogService::logCreated($invoice, 'Invoice generated successfully');

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Invoice generated successfully',
                'invoice' => $invoice->load(['companyPackage.company', 'companyPackage.package', 'discount', 'tax'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to generate invoice', [
                'error' => $e->getMessage(),
                'company_package_id' => $request->company_package_id,
                'user_id' => auth()->id()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate invoice'
            ], 500);
        }
    }

    public function show($id)
    {
        $invoice = CompanyPackageInvoice::with([
            'companyPackage.company',
            'companyPackage.package',
            'discount',
            'tax'
        ])->findOrFail($id);

        $this->authorize('view', $invoice);

        return view('superadmin.invoices.show', compact('invoice'));
    }

    public function markPaid($id, Request $request)
    {
        $invoice = CompanyPackageInvoice::findOrFail($id);
        $this->authorize('markPaid', $invoice);

        if ($invoice->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Invoice is already marked as paid'
            ], 422);
        }

        $request->validate([
            'payment_date' => 'nullable|date'
        ]);

        DB::beginTransaction();
        try {
            $paymentDate = $request->payment_date ? Carbon::parse($request->payment_date) : null;

            $this->billingService->markInvoicePaid($invoice, $paymentDate);

            // Log audit
            AuditLogService::logPaid($invoice, 'Invoice marked as paid successfully');

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Invoice marked as paid successfully',
                'invoice' => $invoice
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to mark invoice as paid', [
                'error' => $e->getMessage(),
                'invoice_id' => $id,
                'user_id' => auth()->id()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark invoice as paid'
            ], 500);
        }
    }

    public function sendInvoice($id)
    {
        $invoice = CompanyPackageInvoice::with(['companyPackage.company'])->findOrFail($id);
        $this->authorize('sendInvoice', $invoice);

        DB::beginTransaction();
        try {
            $this->billingService->sendInvoice($invoice);

            // Log audit
            AuditLogService::logSent($invoice, 'Invoice sent to company successfully');

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Invoice sent successfully',
                'invoice' => $invoice
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to send invoice', [
                'error' => $e->getMessage(),
                'invoice_id' => $id,
                'user_id' => auth()->id()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to send invoice'
            ], 500);
        }
    }
}