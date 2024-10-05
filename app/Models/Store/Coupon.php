<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Models\Store;

use App\DTO\Admin\Invoice\AddCouponToInvoiceItem;
use App\DTO\Admin\Invoice\AddCouponToInvoiceItemDTO;
use App\Models\Account\Customer;
use App\Models\Core\Invoice;
use App\Models\Store\Basket\Basket;
use App\Models\Store\Basket\BasketRow;
use App\Models\Traits\HasMetadata;
use App\Models\Traits\Loggable;
use App\Services\Store\RecurringService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Session;

class Coupon extends Model
{
    use HasFactory;
    use HasMetadata;
    use Loggable;

    const TYPE_FIXED = 'fixed';
    const TYPE_PERCENT = 'percent';

    const APPLIED_MONTH_UNLIMITED = -1;
    const APPLIED_MONTH_FIRST = 0;
    const UNLIMITED_USE = 0;

    protected $fillable = [
        'code',
        'type',
        'applied_month',
        'free_setup',
        'start_at',
        'end_at',
        'first_order_only',
        'max_uses',
        'max_uses_per_customer',
        'usages',
        'unique_use',
        'customer_id',
        'products_required',
        'minimum_order_amount',
        'is_global',
    ];

    protected $casts = [
        'products_required' => 'array',
        'start_at' => 'datetime',
        'end_at' => 'datetime'
    ];

    protected $attributes = [
        'free_setup' => false,
        'first_order_only' => false,
        'is_global' => false,
        'products_required' => '[]',
        'applied_month' => -1,
        'max_uses' => 0,
        'max_uses_per_customer' => 0,
        'usages' => 0,
        'minimum_order_amount' => 0,

    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function usages()
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'coupon_products');
    }

    public function pricing()
    {
        return $this->hasMany(Pricing::class, 'related_id')->where('related_type', 'coupon');
    }

    public function apply(Basket $basket)
    {
        if ($this->type == self::TYPE_FIXED) {
            $discount = $this->free_setup ? $basket->setup_fee : $this->amount;
        } else {
            $discount = $basket->total * $this->amount / 100;
        }
        $basket->discount += $discount;
        $basket->save();
        $this->usages()->create([
            'customer_id' => $basket->customer_id,
            'used_at' => now(),
            'amount' => $discount,
        ]);
    }

    public function isValid(Basket $basket, bool $flash = true)
    {
        if ($this->start_at && $this->start_at->isFuture()) {
            if ($flash) {
                Session::flash("error", __('coupon.coupon_not_started'));
            }
            return false;
        }
        if ($this->end_at && $this->end_at->isPast()) {
            if ($flash) {
                Session::flash("error", __('coupon.coupon_expired'));
            }
            return false;
        }
        if ($this->first_order_only && $basket->user_id != null && Invoice::where('customer_id', $basket->user_id)->where('status', Invoice::STATUS_PAID)->count() > 0) {
            if ($flash) {
                Session::flash("error", __('coupon.first_order_only'));
            }
            return false;
        }
        if ($this->max_uses > 0 && $this->usages()->count() >= $this->max_uses) {
            if ($flash) {
                Session::flash("error", __('coupon.coupon_max_uses'));
            }
            return false;
        }
        if ($this->max_uses_per_customer > 0 && $this->usages()->where('customer_id', $basket->customer_id)->count() >= $this->max_uses_per_customer) {
            if ($flash) {
                Session::flash("error", __('coupon.coupon_max_use_per_customer'));
            }
            return false;
        }
        if ($this->minimum_order_amount > 0 && $basket->subtotalWithoutCoupon() < $this->minimum_order_amount) {
            if ($flash) {
                Session::flash("error", __('coupon.minimum_order_amount', ['amount' => $this->minimum_order_amount]));
            }
            return false;
        }
        if ($this->products_required && !$this->is_global) {
            $products = $basket->rows->map(function ($row) {
                return $row->product_id;
            });
            foreach ($this->products_required as $product) {
                if (!$products->contains($product)) {
                    if ($flash) {
                        Session::flash("error", __('coupon.coupon_not_valid_product'));
                    }
                    return false;
                }
            }
        }
        if (!$this->is_global && $this->products()->count() > 0) {
            $basketRowProductIds = $basket->rows->map(function ($row) {
                return $row->product_id;
            });
            $productIds = $this->products->pluck('id');
            if ($productIds->intersect($basketRowProductIds)->count() == 0) {
                if ($flash) {
                    Session::flash("error", __('coupon.coupon_not_valid_product'));
                }
                return false;
            }
        }
        return true;
    }

    public function getPricingRecurring(string $recurring, string $type)
    {
        if (\Cache::get('coupon_' . $this->id) == null) {
            $pricing = $this->pricing()->first();
            if ($pricing == null) {
                throw new \Exception(sprintf('Coupon Pricing %d not found', $this->id));
            }
            \Cache::put('coupon_' . $this->id, $pricing, 60 * 24);
        }
        $pricing = \Cache::get('coupon_' . $this->id);
        if ($type == BasketRow::PRICE) {
            return $pricing->$recurring;
        }
        $recurring = "setup_". $recurring;
        return $pricing->$recurring;
    }

    public function applyAmount(float $amount, string $recurring, string $type)
    {
        if ($type == BasketRow::SETUP_FEES && $this->free_setup) {
            return 0;
        }
        $value = $this->getPricingRecurring($recurring, $type);
        if ($this->type == self::TYPE_FIXED) {
            return $amount - $value;
        }
        if ($this->type == self::TYPE_PERCENT) {
            return $amount - ($amount * ($value / 100));
        }
    }

    public function discountArray(AddCouponToInvoiceItemDTO $dto)
    {
        $billing = $dto->billing;
        $price = $this->applyAmount($dto->originalUnitPrice, $billing, BasketRow::PRICE);
        $setup = $this->applyAmount($dto->originalUnitSetupfees, $billing, BasketRow::SETUP_FEES);
        $recurrings = app(RecurringService::class)->getRecurrings()->keys();
        $quantity = $dto->quantity;
        $type = $this->type;
        return [
            'code' => $this->code,
            'type' => $this->type,
            'id' => $this->id,
            'applied_month' => $this->applied_month,
            'free_setup' => $this->free_setup,
            'pricing_price' => number_format($this->getPricingRecurring($billing, BasketRow::PRICE) ?? 0, $type == BasketRow::PRICE ? 2 : 0),
            'pricing_setup' => number_format($this->getPricingRecurring($billing, BasketRow::SETUP_FEES) ?? 0, $type == BasketRow::SETUP_FEES ? 2 : 0),
            'discount_unit_price' => number_format($this->applyAmount($dto->originalUnitPrice - $price, $billing, BasketRow::PRICE), 2),
            'discount_unit_setup' => number_format($this->applyAmount($dto->originalUnitSetupfees - $setup, $billing, BasketRow::SETUP_FEES), 2),
            'discount_price' => number_format($this->applyAmount($dto->originalUnitPrice * $quantity - $price * $quantity, $billing, BasketRow::PRICE), 2),
            'discount_setup' => number_format($this->applyAmount($dto->originalUnitSetupfees * $quantity - $setup * $quantity, $billing, BasketRow::SETUP_FEES), 2),
            'discounted_amount' => collect($recurrings)->mapWithKeys(function($recurring) use($dto) {
                if ($dto->product == null) {
                    $recurringprice = $dto->originalUnitPrice;
                    $discountedValue = $this->getPricingRecurring($recurring, BasketRow::PRICE) ?? 0;
                } else {
                    $recurringprice = $dto->product->price;
                    $discountedValue = $this->getPricingRecurring($recurring, BasketRow::PRICE) ?? 0;
                }
                if ($discountedValue == 0){
                    return [$recurring => 0];
                }
                if ($this->type == self::TYPE_PERCENT) {
                    $discountedValue = number_format(($recurringprice * ($discountedValue / 100)), 2);
                }
                return [$recurring => $discountedValue];
            }),
            'values' => collect($recurrings)->mapWithKeys(function($recurring) use($dto) {
                return [$recurring => number_format($this->getPricingRecurring($recurring, BasketRow::PRICE) ?? 0)];
            }),
        ];
    }
}
