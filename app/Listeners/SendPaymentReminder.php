<?php

namespace App\Listeners;

use App\Events\PaymentReminder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendPaymentReminder implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(PaymentReminder $event): void
    {
        $invoice = $event->invoice;

        Log::info("Payment reminder notification", [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'company_id' => $invoice->companyPackage->company_id,
            'due_date' => $invoice->due_date,
            'reminder_amount' => $invoice->total_amount
        ]);

        // Here you would send payment reminder notifications
        // For example:
        // Mail::to($invoice->companyPackage->company->email)->send(new PaymentReminderMail($invoice));
    }
}