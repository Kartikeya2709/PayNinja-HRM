<?php

namespace App\Console\Commands;

use App\Models\CompanyPackage;
use App\Services\BillingService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class GenerateInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:generate {--billing-cycle=monthly : The billing cycle (monthly, yearly, quarterly)} {--dry-run : Show what would be generated without creating invoices}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate invoices for active company packages based on billing cycle';

    protected BillingService $billingService;

    public function __construct(BillingService $billingService)
    {
        parent::__construct();
        $this->billingService = $billingService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $billingCycle = $this->option('billing-cycle');
        $dryRun = $this->option('dry-run');

        $this->info("Generating {$billingCycle} invoices...");

        // Get active company packages that need billing
        $companyPackages = $this->getCompanyPackagesForBilling($billingCycle);

        if ($companyPackages->isEmpty()) {
            $this->info('No company packages found that need billing.');
            return;
        }

        $this->info("Found {$companyPackages->count()} company packages to bill.");

        $generatedCount = 0;
        $skippedCount = 0;

        foreach ($companyPackages as $companyPackage) {
            try {
                // Calculate billing period
                $billingPeriod = $this->calculateBillingPeriod($companyPackage, $billingCycle);

                if (!$billingPeriod) {
                    $this->warn("Skipping company package {$companyPackage->id} - could not calculate billing period.");
                    $skippedCount++;
                    continue;
                }

                // Check if invoice already exists for this period
                $existingInvoice = $companyPackage->invoices()
                    ->where('billing_period_start', $billingPeriod['start'])
                    ->where('billing_period_end', $billingPeriod['end'])
                    ->first();

                if ($existingInvoice) {
                    $this->warn("Invoice already exists for company package {$companyPackage->id} for period {$billingPeriod['start']->format('Y-m-d')} to {$billingPeriod['end']->format('Y-m-d')}");
                    $skippedCount++;
                    continue;
                }

                if ($dryRun) {
                    $this->info("[DRY RUN] Would generate invoice for company package {$companyPackage->id} ({$companyPackage->package->name}) for period {$billingPeriod['start']->format('Y-m-d')} to {$billingPeriod['end']->format('Y-m-d')}");
                } else {
                    $invoice = $this->billingService->generateInvoice(
                        $companyPackage,
                        $billingPeriod['start'],
                        $billingPeriod['end']
                    );

                    // Send invoice
                    $this->billingService->sendInvoice($invoice);

                    $this->info("Generated invoice {$invoice->invoice_number} for company package {$companyPackage->id}");
                }

                $generatedCount++;

            } catch (\Exception $e) {
                $this->error("Failed to generate invoice for company package {$companyPackage->id}: {$e->getMessage()}");
                $skippedCount++;
            }
        }

        $this->info("Invoice generation completed. Generated: {$generatedCount}, Skipped: {$skippedCount}");
    }

    /**
     * Get company packages that need billing
     */
    protected function getCompanyPackagesForBilling(string $billingCycle)
    {
        return CompanyPackage::active()
            ->whereHas('package', function ($query) use ($billingCycle) {
                $query->where('billing_cycle', $billingCycle)
                      ->orWhere(function ($q) use ($billingCycle) {
                          // If package has no specific billing cycle, default to monthly
                          $q->whereNull('billing_cycle')
                            ->where('pricing_type', 'subscription');
                      });
            })
            ->with(['package', 'company'])
            ->get();
    }

    /**
     * Calculate billing period for a company package
     */
    protected function calculateBillingPeriod(CompanyPackage $companyPackage, string $billingCycle): ?array
    {
        $now = now();

        // Find the last paid invoice to determine next billing period
        $lastPaidInvoice = $companyPackage->invoices()
            ->paid()
            ->orderBy('billing_period_end', 'desc')
            ->first();

        if ($lastPaidInvoice) {
            $periodStart = $lastPaidInvoice->billing_period_end->copy()->addDay();
        } else {
            // First invoice - start from activation date or assigned date
            $startDate = $companyPackage->activated_at ?: $companyPackage->assigned_at;
            if (!$startDate) {
                return null;
            }
            $periodStart = Carbon::parse($startDate)->startOfMonth();
        }

        // Calculate period end based on billing cycle
        $periodEnd = $periodStart->copy();

        switch ($billingCycle) {
            case 'yearly':
                $periodEnd->addYear()->subDay();
                break;
            case 'quarterly':
                $periodEnd->addMonths(3)->subDay();
                break;
            case 'monthly':
            default:
                $periodEnd->addMonth()->subDay();
                break;
        }

        // Only generate if the billing period has ended
        if ($periodEnd->isFuture()) {
            return null;
        }

        return [
            'start' => $periodStart,
            'end' => $periodEnd,
        ];
    }
}