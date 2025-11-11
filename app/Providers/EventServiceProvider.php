<?php

namespace App\Providers;

use App\Events\InvoiceGenerated;
use App\Events\InvoicePaid;
use App\Events\InvoiceOverdue;
use App\Events\PaymentReminder;
use App\Listeners\SendInvoiceNotification;
use App\Listeners\SendPaymentConfirmation;
use App\Listeners\SendOverdueNotification;
use App\Listeners\SendPaymentReminder;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        InvoiceGenerated::class => [
            SendInvoiceNotification::class,
        ],

        InvoicePaid::class => [
            SendPaymentConfirmation::class,
        ],

        InvoiceOverdue::class => [
            SendOverdueNotification::class,
        ],

        PaymentReminder::class => [
            SendPaymentReminder::class,
        ],
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
