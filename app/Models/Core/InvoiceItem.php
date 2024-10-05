<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Models\Core;

use App\Casts\JsonToObject;
use App\Contracts\Store\ProductTypeInterface;
use App\DTO\Store\ProductDataDTO;
use App\Models\Provisioning\Service;
use App\Models\Provisioning\ServiceRenewals;
use App\Models\Store\Product;
use App\Models\Traits\HasMetadata;
use App\Services\Core\InvoiceService;
use Database\Factories\Core\InvoiceItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Str;

class InvoiceItem extends Model
{
    use HasFactory;
    use HasMetadata;
    protected $fillable = [
        'invoice_id',
        'description',
        'name',
        'quantity',
        'unit_price',
        'discount',
        'unit_setupfees',
        'unit_original_price',
        'unit_original_setupfees',
        'type',
        'related_id',
        'delivered_at',
        'cancelled_at',
        'refunded_at',
        'data',
    ];

    protected $attributes = [
        'data' => '[]',
        'discount' => '[]',
    ];

    protected $casts = [
        'data' => 'array',
        'discount' => JsonToObject::class,
        'unit_price' => 'float',
        'unit_original_price' => 'float',
        'unit_original_setupfees' => 'float',
        'unit_setupfees' => 'float',
        'quantity' => 'integer',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'refunded_at' => 'datetime',
        'invoice_id' => 'integer',
        'related_id' => 'integer',
        'type' => 'string',
    ];

    protected static function boot()
    {
        parent::boot();
        static::updating(function(InvoiceItem $item){
            if ($item->type == CustomItem::CUSTOM_ITEM){
                $customItem = CustomItem::find($item->related_id);
                if ($customItem == null){
                    return;
                }
                $customItem->update($item->only('name', 'description', 'unit_price', 'unit_setupfees'));
            }
        });
        static::deleted(function(InvoiceItem $item){

            if ($item->type == 'renewal') {
                $service = Service::find($item->related_id);
                $service->update(['invoice_id' => null]);
            }
            if ($item->type == CustomItem::CUSTOM_ITEM){
                CustomItem::find($item->related_id)->delete();
            }
        });
    }

    public function invoice() {
        return $this->belongsTo(Invoice::class);
    }

    public function cancel()
    {
        $this->cancelled_at = now();
        $this->save();
    }

    public function refund()
    {
        $this->refunded_at = now();
        $this->save();
    }

    public function price()
    {
        return $this->unit_price * $this->quantity + $this->unit_setupfees * $this->quantity;
    }

    public function canDisplayDescription()
    {
        if (Str::startsWith($this->description, 'Created from') || Str::startsWith($this->description, 'Add extra')) {
            return false;
        }
        return $this->description != $this->name;
    }

    /**
     * @return Product
     * @throws \Exception
     */
    public function relatedType()
    {
        if (in_array($this->type, ProductTypeInterface::ALL)) {
            return Product::find($this->related_id);
        }
        if ($this->type == 'renewal') {
            return Service::find($this->related_id);
        }
        if ($this->type == CustomItem::CUSTOM_ITEM){
            return CustomItem::find($this->related_id);
        }
        throw new \Exception('InvoiceItem : Unknown type ' . $this->type);
    }

    public function billing()
    {
        return $this->data['billing'] ?? 'monthly';
    }

    public function renderHTML(bool $inAdmin = false)
    {
        if ($this->relatedType() instanceof Product) {
            if ($this->relatedType()->productType()->data($this->relatedType()) == null)
                return '';
            return $this->relatedType()->productType()->data($this->relatedType())->render(new ProductDataDTO($this->relatedType(), $this->data + ['in_admin' => true], [], []));
        }
    }


    protected static function newFactory()
    {
        return InvoiceItemFactory::new();
    }

    public static function findServicesMustDeliver():Collection
    {
        return self::where('delivered_at', null)
            ->where('cancelled_at', null)
            ->where('refunded_at', null)
            ->where('type', 'service')
            ->select('invoice_items.*')
            ->where('invoices.status', Invoice::STATUS_PAID)
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->get();
    }

    public static function findPendingRenewals():Collection
    {
        return self::where('delivered_at', null)
            ->where('cancelled_at', null)
            ->where('refunded_at', null)
            ->where('type', 'renewal')
            ->select('invoice_items.*')
            ->where('invoices.status', Invoice::STATUS_PAID)
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->get();
    }

    public function hasDiscount()
    {
        if ($this->discount == null){
            return false;
        }
        return !empty($this->discount);
    }

    public function getDiscount(bool $force = true)
    {
        if (!$this->hasDiscount()){
            $default = new \stdClass();
            $default->discount_price = 0;
            $default->discount_setup = 0;
            $default->pricing_price = 0;
            $default->pricing_setup = 0;
            $default->type = 'fixed';
            $default->code = '';
            if ($force){
                return $default;
            }
            return null;
        }
        if (is_object($this->discount)){
            return $this->discount;
        }
        return json_decode($this->discount);

    }

    public function getDiscountLabel()
    {
        $discount = $this->getDiscount();
        if ($discount === null){
            return null;
        }
        $code = $discount->code;
        if ($discount->type == 'fixed'){
            return __('coupon.coupon_label', ['code' => $code, 'discount' => '-'. formatted_price($discount->pricing_price, $this->invoice->currency)]);
        }
        return __('coupon.coupon_label', ['code' => $code, 'discount' => '-'. $discount->pricing_price . '%']);
    }

    public function discountTotal()
    {
        $discount = $this->getDiscount();
        if ($discount === null){
            return 0;
        }
        return $discount->discount_price + $discount->discount_setup;
    }

    public function couponId()
    {
        $discount = $this->getDiscount();
        if ($discount === null){
            return null;
        }
        return $discount->id ?? null;
    }

    public function tryDeliver()
    {
        if ($this->type == 'renewal'){
            $service = $this->relatedType();
            if ($service == null){
                throw new \Exception("Service not found for invoice item {$this->id}");
            }
            $service->renew($this);
            $this->delivered_at = now();
            $this->save();
            ServiceRenewals::where('invoice_id', $this->invoice_id)->update(['renewed_at' => now()]);
            return true;
        } else if ($this->type == 'service'){
            $services = $this->getMetadata('services');
            if ($services == null) {
                try {
                    InvoiceService::createServicesFromInvoiceItem($this->invoice, $this);
                } catch (\Exception $e) {
                    throw new \Exception("Error creating services for invoice item {$this->id} : " . $e->getMessage());
                }
            }
            $delivered = [];
            $services = explode(',', $services);
            foreach ($services as $serviceId) {
                $service = Service::find($serviceId);
                if ($service == null) {
                    throw new \Exception("Service {$serviceId} not found for invoice item {$this->id}");
                }
                if ($service->status == 'active'){
                    $delivered[] = $service->id;
                    continue;
                }
                $result = $service->deliver();
                if ($result->success){
                    $delivered[] = $service->id;
                } else {
                    throw new \Exception("Service {$service->id} delivery failed Error : " . $service->delivery_errors);
                }
            }
            if (count($delivered) == count($services)){
                $this->delivered_at = now();
                $this->save();
                return true;
            }
        }
        return false;
    }
}
