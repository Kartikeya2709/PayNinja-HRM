<?php

namespace App\Services;

use App\Models\CompanyPackage;
use App\Models\CompanyPackageInvoice;
use App\Models\Discount;
use App\Models\Tax;
use App\Events\InvoiceGenerated;
use App\Events\InvoicePaid;
use App\Events\InvoiceOverdue;
use App\Events\PaymentReminder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BillingService
{
    protected PricingService $pricingService;

    public function __construct(PricingService $pricingService)
    {
        $this->pricingService = $pricingService;
    }

    /**
     * Generate invoice for billing period
     */
    public function generateInvoice(CompanyPackage $companyPackage, Carbon $billingPeriodStart, Carbon $billingPeriodEnd): CompanyPackageInvoice
    {
        // Calculate invoice amounts
        $invoiceData = $this->calculateInvoiceTotal($companyPackage, $billingPeriodStart, $billingPeriodEnd);

        // Generate unique invoice number
        $invoiceNumber = $this->generateInvoiceNumber();

        // Determine due date (30 days from now)
        $dueDate = now()->addDays(30);

        $invoice = CompanyPackageInvoice::create([
            'company_package_id' => $companyPackage->id,
            'invoice_number' => $invoiceNumber,
            'billing_period_start' => $billingPeriodStart,
            'billing_period_end' => $billingPeriodEnd,
            'subtotal' => $invoiceData['subtotal'],
            'discount_amount' => $invoiceData['discount_amount'],
            'tax_amount' => $invoiceData['tax_amount'],
            'total_amount' => $invoiceData['total'],
            'currency' => $invoiceData['currency'],
            'status' => 'draft',
            'due_date' => $dueDate,
            'discount_id' => $invoiceData['discount']?->id,
            'tax_id' => $invoiceData['tax']?->id,
        ]);

        // Fire event
        event(new InvoiceGenerated($invoice));

        Log::info("Invoice generated", [
            'invoice_id' => $invoice->id,
            'company_package_id' => $companyPackage->id,
            'total' => $invoice->total_amount
        ]);

        return $invoice;
    }

    /**
     * Calculate invoice amounts
     */
    public function calculateInvoiceTotal(CompanyPackage $companyPackage, Carbon $billingPeriodStart, Carbon $billingPeriodEnd): array
    {
        // For now, assume we need to get user count - in real app, this would be calculated based on usage
        // For simplicity, let's assume we have a method to get user count for the period
        $userCount = $this->getUserCountForPeriod($companyPackage, $billingPeriodStart, $billingPeriodEnd);

        // Calculate pricing using PricingService
        return $this->pricingService->calculatePackagePrice($companyPackage->package, $userCount);
    }

    /**
     * Send invoice via email
     */
    public function sendInvoice(CompanyPackageInvoice $invoice): bool
    {
        try {
            // Update status to sent
            $invoice->update(['status' => 'sent']);

            // Send email (placeholder - implement actual email sending)
            // Mail::to($invoice->companyPackage->company->email)->send(new InvoiceMail($invoice));

            Log::info("Invoice sent", ['invoice_id' => $invoice->id]);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send invoice", [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Mark invoice as paid
     */
    public function markInvoicePaid(CompanyPackageInvoice $invoice, ?Carbon $paymentDate = null): bool
    {
        try {
            $paymentDate = $paymentDate ?: now();

            $invoice->update([
                'status' => 'paid',
                'paid_at' => $paymentDate,
            ]);

            // Increment discount usage if applicable
            if ($invoice->discount) {
                $invoice->discount->increment('usage_count');
            }

            // Fire event
            event(new InvoicePaid($invoice));

            Log::info("Invoice marked as paid", [
                'invoice_id' => $invoice->id,
                'paid_at' => $paymentDate
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to mark invoice as paid", [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Generate unique invoice numbers
     */
    public function generateInvoiceNumber(): string
    {
        do {
            $number = 'INV-' . date('Y') . '-' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (CompanyPackageInvoice::where('invoice_number', $number)->exists());

        return $number;
    }

    /**
     * Get user count for billing period (placeholder - implement based on your business logic)
     */
    protected function getUserCountForPeriod(CompanyPackage $companyPackage, Carbon $start, Carbon $end): int
    {
        // Placeholder implementation - in real app, calculate based on company user count during period
        // For now, return a default value
        return 10; // This should be calculated based on actual usage
    }

    /**
     * Check for overdue invoices and send reminders
     */
    public function processOverdueInvoices(): void
    {
        $overdueInvoices = CompanyPackageInvoice::overdue()->get();

        foreach ($overdueInvoices as $invoice) {
            // Update status to overdue if not already
            if ($invoice->status !== 'overdue') {
                $invoice->update(['status' => 'overdue']);
                event(new InvoiceOverdue($invoice));
            }

            // Send payment reminder
            event(new PaymentReminder($invoice));
        }
    }
}