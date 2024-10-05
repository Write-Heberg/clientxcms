<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Listeners\Core;

use App\Events\Core\Invoice\InvoiceCompleted;
use App\Models\Provisioning\ServiceRenewals;
use App\Services\Core\InvoiceService;

class RenewServiceListerner
{

    public function handle(InvoiceCompleted $event): void
    {
        $invoice = $event->invoice;
        foreach ($invoice->items as $item){
            if ($item->type == 'renewal' && $item->delivered_at != null) {
                $service = $item->relatedType();
                $service->renew($item);
                $item->delivered_at = now();
                $item->save();
                ServiceRenewals::where('invoice_id', $invoice->id)->update(['renewed_at' => now()]);
            }
        }
    }
}
