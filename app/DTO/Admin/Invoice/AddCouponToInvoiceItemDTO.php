<?php

namespace App\DTO\Admin\Invoice;

use App\Models\Core\Invoice;
use App\Models\Core\InvoiceItem;
use App\Models\Store\Product;

class AddCouponToInvoiceItemDTO
{
    public float $originalUnitPrice;
    public float $originalUnitSetupfees;
    public int $quantity;
    public string $billing;
    public ?Product $product = null;
    public Invoice $invoice;
    public InvoiceItem $invoiceItem;

    public function __construct(array $params, InvoiceItem $invoiceItem, ?Product $product = null)
    {
        $this->originalUnitPrice = $params['unit_price'];
        $this->originalUnitSetupfees = $params['unit_setupfees'];
        $this->quantity = $params['quantity'];
        $this->billing = $params['billing'] ?? $invoiceItem->data['billing'] ?? 'monthly';
        $this->product = $product;
        $this->invoiceItem = $invoiceItem;
        $this->invoice = $invoiceItem->invoice;
    }
}
