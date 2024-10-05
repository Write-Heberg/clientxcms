<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Listeners\Core;

use App\Events\Core\Invoice\InvoiceCompleted;
use App\Events\Core\Invoice\InvoiceCreated;
use App\Mail\Core\Invoice\InvoiceCreatedEmail;
use App\Mail\Core\Invoice\InvoicePaidEmail;
use App\Models\Core\Invoice;
use Illuminate\Notifications\RoutesNotifications;

class SendInvoiceNotification
{

    use RoutesNotifications;
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     * @param  InvoiceCreated|InvoiceCompleted  $event
     */
    public function handle($event): void
    {
        if ($event instanceof InvoiceCreated) {
            $this->sendInvoiceCreatedNotification($event->invoice);
        } elseif ($event instanceof InvoiceCompleted) {
            $this->sendInvoiceCompletedNotification($event->invoice);
        }
    }

    private function sendInvoiceCreatedNotification(Invoice $invoice): void
    {
        $invoice->customer->notify(new InvoiceCreatedEmail($invoice));
    }

    private function sendInvoiceCompletedNotification(Invoice $invoice): void
    {
        $invoice->customer->notify(new InvoicePaidEmail($invoice));
    }
}
