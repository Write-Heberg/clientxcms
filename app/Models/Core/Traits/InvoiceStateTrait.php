<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Models\Core\Traits;

use App\Events\Core\CheckoutCompletedEvent;
use App\Events\Core\Invoice\InvoiceCancelled;
use App\Events\Core\Invoice\InvoiceCompleted;
use App\Events\Core\Invoice\InvoiceFailed;
use App\Events\Core\Invoice\InvoiceRefunded;
use App\Models\Core\Invoice;
use App\Models\Core\InvoiceItem;
use App\Models\Store\Basket\Basket;
use App\Services\Core\InvoiceService;

trait InvoiceStateTrait
{

    public function cancel(bool $clearBasket = true)
    {
        if ($this->status === self::STATUS_CANCELLED) {
            return;
        }
        $this->status = self::STATUS_CANCELLED;
        $this->save();
        $this->items->map(function (InvoiceItem $item) {
            $item->cancel();
        });
        $this->clearBasket($clearBasket);
        event(new InvoiceCancelled($this));
    }

    public function complete(bool $clearBasket = true)
    {
        if ($this->status === self::STATUS_PAID) {
            return;
        }

        if (InvoiceService::getBillingType() == InvoiceService::PRO_FORMA) {
            $date = $this->created_at->format('Y-m');
            $this->invoice_number = Invoice::generateInvoiceNumber($date, false);
        }
        $this->paid_at = now();
        $this->status = self::STATUS_PAID;
        $this->save();
        $this->clearBasket($clearBasket);
        event(new InvoiceCompleted($this));
    }

    public function refund(bool $clearBasket = true)
    {
        if ($this->status === self::STATUS_REFUNDED) {
            return;
        }
        $this->status = self::STATUS_REFUNDED;
        $this->save();

        $this->items->map(function (InvoiceItem $item) {
            $item->refund();
        });
        $this->clearBasket($clearBasket);
        event(new InvoiceRefunded($this));
    }

    public function fail(bool $clearBasket = true)
    {
        if ($this->status === self::STATUS_FAILED) {
            return;
        }
        $this->status = self::STATUS_FAILED;
        $this->save();

        $this->items->map(function (InvoiceItem $item) {
            $item->cancel();
        });
        $this->clearBasket($clearBasket);
        event(new InvoiceFailed($this));
    }

    private function clearBasket(bool $clearBasket = true)
    {
        if ($clearBasket) {
            if ($this->getMetadata('basket') !== null) {
                $basket = Basket::find($this->getMetadata('basket') ?? Basket::getBasket());
                if ($basket->completed_at !== null) {
                    return;
                }
                event(new CheckoutCompletedEvent($basket, $this));
                $basket->clear(true);
            }
        }
    }
}
