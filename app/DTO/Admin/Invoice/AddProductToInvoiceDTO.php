<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\DTO\Admin\Invoice;

use App\Models\Core\Invoice;
use App\Models\Store\Product;
use App\Services\Store\TaxesService;
use DragonCode\Contracts\Support\Arrayable;

class AddProductToInvoiceDTO implements Arrayable {

    public Invoice $invoice;
    public Product $product;
    public int $quantity;
    public float $unitPrice;
    public float $unitSetupfees;
    public string $name;
    public ?string $description = null;
    public array $itemData;

    public function __construct(Invoice $invoice, Product $product, array $validatedData, array $itemData = [])
    {
        $this->invoice = $invoice;
        $this->product = $product;
        $this->quantity = $validatedData['quantity'];
        $this->unitPrice = $validatedData['unit_price'];
        $this->unitSetupfees = $validatedData['unit_setupfees'];
        $this->name = $validatedData['name'];
        $this->description = $validatedData['description'] ?? '';
        $this->itemData = $itemData;
        if (array_key_exists('billing', $validatedData)) {
            $this->itemData['billing'] = $validatedData['billing'];
        }
    }

    public function toArray(): array
    {
        return [
            'invoice_id' => $this->invoice->id,
            'name' => $this->name,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice,
            'unit_setupfees' => $this->unitSetupfees,
            'total' => $this->total(),
            'tax' => $this->tax(),
            'subtotal' => $this->subtotal(),
            'setupfee' => $this->setup(),
            'type' => 'service',
            'related_id' => $this->product->id,
            'data' => $this->itemData,
            'unit_original_price' => $this->unitPrice,
            'unit_original_setupfees' => $this->unitSetupfees,
        ];
    }

    public function recurringPayment(bool $withQuantity = true)
    {
        if (!$withQuantity) {
            return $this->unitPrice;
        }
        return $this->unitPrice * $this->quantity;
    }

    public function setup(bool $withQuantity = true)
    {
        if (!$withQuantity) {
            return $this->unitSetupfees;
        }
        return $this->unitSetupfees * $this->quantity;
    }

    public function tax()
    {
        return TaxesService::getTaxAmount($this->subtotal(), tax_percent());
    }

    public function subtotal()
    {
        return $this->recurringPayment() + $this->setup();
    }

    public function total()
    {
        return $this->subtotal() + $this->tax();
    }
}
