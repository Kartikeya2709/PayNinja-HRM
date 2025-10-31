<?php

namespace App\Events;

use App\Models\CompanyPackageInvoice;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InvoicePaid
{
    use Dispatchable, SerializesModels;

    public CompanyPackageInvoice $invoice;

    public function __construct(CompanyPackageInvoice $invoice)
    {
        $this->invoice = $invoice;
    }
}