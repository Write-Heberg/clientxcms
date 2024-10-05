<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Models\Core;

use App\Abstracts\SupportRelateItemTrait;
use App\Contracts\Helpdesk\SupportRelateItemInterface;
use App\DTO\Admin\Invoice\AddProductToInvoiceDTO;
use App\Exceptions\WrongPaymentException;
use App\Models\Account\Customer;
use App\Models\Core\Traits\InvoiceStateTrait;
use App\Models\Provisioning\Service;
use App\Models\Store\Product;
use App\Models\Traits\HasMetadata;
use App\Models\Traits\Loggable;
use App\Services\Core\InvoiceService;
use App\Services\Store\TaxesService;
use Database\Factories\Core\InvoiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Invoice extends Model implements SupportRelateItemInterface
{
    use HasFactory, InvoiceStateTrait, HasMetadata, SupportRelateItemTrait, Loggable;

    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_DRAFT = 'draft';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REFUNDED = 'refunded';
    const STATUS_FAILED = 'failed';

    const FILTERS = [
        'all' => 'all',
        self::STATUS_PENDING => 'pending',
        self::STATUS_PAID => 'paid',
        self::STATUS_CANCELLED => 'cancelled',
        self::STATUS_REFUNDED => 'refunded',
        self::STATUS_FAILED => 'failed',
    ];

    protected $fillable = [
        'customer_id',
        'due_date',
        'total',
        'subtotal',
        'tax',
        'setupfees',
        'currency',
        'status',
        'external_id',
        'notes',
        'paymethod',
        'fees',
        'invoice_number',
        'paid_at',
    ];

    protected $casts = [
        'discount' => 'array',
        'due_date' => 'datetime',
        'created_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'pending'
    ];

    public static function getStatuses()
    {
        return [
            self::STATUS_PENDING => __('global.states.pending'),
            self::STATUS_PAID => __('global.states.paid'),
            self::STATUS_CANCELLED => __('global.states.cancelled'),
            self::STATUS_REFUNDED => __('global.states.refunded'),
            self::STATUS_FAILED => __('global.states.failed'),
            self::STATUS_DRAFT => __('global.states.draft'),
        ];
    }

    public function isDraft()
    {
        return $this->status == self::STATUS_DRAFT;
    }

    public function gateway()
    {
        return $this->belongsTo(Gateway::class, 'paymethod', 'uuid');
    }

    public function addService(Service $service)
    {
        InvoiceService::appendServiceOnExistingInvoice($service, $this);
        if ($service->invoice_id != $this->id && $service->invoice_id != null){
            $service->update(['invoice_id' => $this->id]);
            Invoice::find($service->invoice_id)->cancel();
        }
        $service->update(['invoice_id' => $this->id]);

    }

    public function addProduct(Product $product, array $validatedData, array $productData)
    {
        InvoiceService::appendProductOnExistingInvoice(new AddProductToInvoiceDTO($this, $product, $validatedData, $productData));
    }

    public function items() {
        return $this->hasMany(InvoiceItem::class);
    }

    public function customer() {
        return $this->belongsTo(Customer::class);
    }

    public function pay(Gateway $gateway, Request $request)
    {
        if ($this->total == 0){
            if ($gateway->uuid != 'balance'){
                throw new WrongPaymentException('Only balance payment is allowed for this invoice.');
            }
        }
        $this->update(['paymethod' => $gateway->uuid]);
        return $gateway->createPayment($this, $request);
    }

    public function identifier()
    {
        return $this->invoice_number;
    }

    public function canPay()
    {
        return $this->status == self::STATUS_PENDING;
    }

    public function download()
    {
        $pdf = \PDF::loadView('front.client.invoices.pdf', ['invoice' => $this, 'customer' => $this->customer, 'countries' => \App\Helpers\Countries::names()]);
        return $pdf->download($this->identifier() . '.pdf');
    }

    public function pdf()
    {
        $pdf = \PDF::loadView('front.client.invoices.pdf', ['invoice' => $this, 'customer' => $this->customer, 'countries' => \App\Helpers\Countries::names()]);
        return $pdf->stream($this->identifier() . '.pdf');
    }

    public function clearServiceAssociation()
    {
        $services = Service::where('invoice_id', $this->id)->get();
        foreach ($services as $service){
             $service->update(['invoice_id' => null]);
        }
    }

    public function addCustomProduct(array $validatedData)
    {
        $id = CustomItem::create([
            'name' => $validatedData['name'],
            'description' => $validatedData['description'],
            'unit_price' => $validatedData['unit_price'],
            'unit_setupfees' => $validatedData['unit_setupfees']
        ])->id;
        InvoiceItem::create([
            'name' => $validatedData['name'],
            'description' => $validatedData['description'],
            'quantity' => $validatedData['quantity'],
            'unit_price' => $validatedData['unit_price'],
            'unit_setupfees' => $validatedData['unit_setupfees'],
            'invoice_id' => $this->id,
            'type' => 'custom_item',
            'related_id' => $id,
            'data' => [],
            'unit_original_price' => $validatedData['unit_price'],
            'unit_original_setupfees' => $validatedData['unit_setupfees'],
        ]);
    }

    protected static function newFactory()
    {
        return InvoiceFactory::new();
    }

    public function recalculate(bool $coupon = false)
    {
        $subtotal = 0;
        $setupfees = 0;
        foreach ($this->items as $item) {
            $subtotal += $item->price() - $item->discountTotal();
            $setupfees += $item->unit_setupfees * $item->quantity;
        }
        $vat = TaxesService::getTaxAmount($subtotal, tax_percent());
        $this->total = $subtotal + $vat;
        $this->subtotal = $subtotal;
        $this->tax = $vat;
        $this->setupfees = $setupfees;
        $this->save();
    }

    public function getDiscountTotal()
    {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item->discountTotal();
        }
        return $total;
    }
    public function relatedName():string
    {
        return __('global.invoice') . ' #' . $this->id . ' - ' . $this->total . ' ' . currency_symbol($this->currency);
    }

    public static function generateInvoiceNumber(?string $date = null, bool $creation = true, int $add = 1):string
    {
        $prefix = setting('billing_invoice_prefix', 'CTX');
        $key = $date ?? now()->format('Y-m');
        if ($creation && InvoiceService::getBillingType() == InvoiceService::PRO_FORMA){
            $prefix =  "$prefix-PROFORMA-" . str_pad(Invoice::where('invoice_number', 'like', $prefix . "-PROFORMA-" . $key . "%")->count() + $add, 4, '0', STR_PAD_LEFT);
        } else {
            $prefix =  $prefix . "-" . $key . "-" . str_pad(Invoice::where('invoice_number', 'like', $prefix . "-" . $key . "%")->count() + $add, 4, '0', STR_PAD_LEFT);
        }
        if (Invoice::where('invoice_number', $prefix)->exists()){
            return self::generateInvoiceNumber($date, $creation, $add + 1);
        }
        return $prefix;
    }

    public static function updateInvoicePrefix(string $new):void
    {
        $all = Invoice::where('invoice_number', 'like', setting('billing_invoice_prefix', 'CTX') . "%")->get();
        foreach ($all as $invoice){
            $invoice->update(['invoice_number' => str_replace(setting('billing_invoice_prefix', 'CTX'), $new, $invoice->invoice_number)]);
        }
    }
}
