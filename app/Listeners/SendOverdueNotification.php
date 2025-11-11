<?php

namespace App\Listeners;

use App\Events\InvoiceOverdue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendOverdueNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(InvoiceOverdue $event): void
    {
        $invoice = $event->invoice;

        Log::info("Overdue invoice notification", [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'company_id' => $invoice->companyPackage->company_id,
            'due_date' => $invoice->due_date,
            'overdue_amount' => $invoice->total_amount
        ]);

        // Here you would send overdue payment notifications
        // For example:
        // Mail::to($invoice->companyPackage->company->email)->send(new OverdueInvoiceMail($invoice));
    }
}