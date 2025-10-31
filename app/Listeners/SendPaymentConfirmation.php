<?php

namespace App\Listeners;

use App\Events\InvoicePaid;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendPaymentConfirmation implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(InvoicePaid $event): void
    {
        $invoice = $event->invoice;

        Log::info("Payment confirmation notification", [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'company_id' => $invoice->companyPackage->company_id,
            'paid_amount' => $invoice->total_amount,
            'paid_at' => $invoice->paid_at
        ]);

        // Here you would send payment confirmation notifications
        // For example:
        // Mail::to($invoice->companyPackage->company->email)->send(new PaymentConfirmationMail($invoice));
    }
}