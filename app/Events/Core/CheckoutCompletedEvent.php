<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Events\Core;

use App\Models\Core\Invoice;
use App\Models\Store\Basket\Basket;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CheckoutCompletedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Basket $basket;
    public Invoice $invoice;

    public function __construct(Basket $basket, Invoice $invoice)
    {
        $this->basket = $basket;
        $this->invoice = $invoice;
    }
}
