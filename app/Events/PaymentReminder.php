<?php

namespace App\Events;

use App\Models\CompanyPackageInvoice;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentReminder
{
    use Dispatchable, SerializesModels;

    public CompanyPackageInvoice $invoice;

    public function __construct(CompanyPackageInvoice $invoice)
    {
        $this->invoice = $invoice;
    }
}