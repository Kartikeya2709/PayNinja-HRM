<?php

namespace App\Listeners;

use App\Events\InvoiceGenerated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendInvoiceNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(InvoiceGenerated $event): void
    {
        $invoice = $event->invoice;

        Log::info("Invoice generated notification", [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'company_id' => $invoice->companyPackage->company_id,
            'total_amount' => $invoice->total_amount
        ]);

        // Here you would send notifications (email, SMS, etc.)
        // For example:
        // Mail::to($invoice->companyPackage->company->email)->send(new InvoiceGeneratedMail($invoice));
    }
}