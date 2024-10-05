<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Listeners\Core;

use App\Events\Core\Invoice\InvoiceCompleted;
use App\Services\Core\InvoiceService;

class CreateServiceListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     * @throws \Exception
     */
    public function handle(InvoiceCompleted $event): void
    {
        $invoice = $event->invoice;
        foreach ($invoice->items as $item){
            if ($item->type == 'service') {
                InvoiceService::createServicesFromInvoiceItem($invoice, $item);
            }
        }
    }
}
