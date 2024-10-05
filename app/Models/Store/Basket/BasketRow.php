<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Models\Store\Basket;

use App\DTO\Store\ProductDataDTO;
use App\Models\Store\Coupon;
use App\Models\Store\Product;
use App\Services\Store\RecurringService;
use App\Services\Store\TaxesService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BasketRow extends Model
{
    use HasFactory;

    use BasketRowCouponTrait;

    const PRICE = 'price';
    const SETUP_FEES = 'setup';

    protected $table = 'baskets_rows';

    protected $fillable = [
        'basket_id',
        'product_id',
        'options',
        'quantity',
        'data',
        'billing',
        'currency',
    ];

    protected $casts = [
        'options' => 'array',
        'data' => 'array',
        'quantity' => 'integer',
    ];

    protected $attributes = [
        'options' => '{}',
        'data' => '{}',
        'billing' => 'monthly',
        'quantity' => 1,
        'currency' => 'eur'
    ];

    public function applyCoupon(float $price, string $type)
    {
        /** @var Coupon $coupon */
        $coupon = $this->basket->coupon;
        if ($coupon != null && $this->enableCoupon) {
            if ($this->canApplyCoupon($coupon)) {
                return $coupon->applyAmount($price, $this->billing, $type);
            }
        }
        return $price;
    }

    /**
     * Permet de donner le prix de renouvellement sans les taxes
     * @return mixed
     */
    public function recurringPayment(bool $withQuantity = true)
    {
        if ($this->billing == 'onetime') {
            return 0;
        }
        $this->enableCoupon();
        if (!$withQuantity){
            $recurringPayment = $this->product->getPriceByCurrency($this->currency, $this->billing)->price;
        } else {
            $recurringPayment = $this->product->getPriceByCurrency($this->currency, $this->billing)->price * $this->quantity;
        }
        return $this->applyCoupon($recurringPayment, self::PRICE);
    }

    public function onetimePayment(bool $withQuantity = true)
    {
        if ($this->billing != 'onetime') {
            return 0;
        }
        $this->enableCoupon();
        if (!$withQuantity){
            $onetimePayment = $this->product->getPriceByCurrency($this->currency, $this->billing)->price;
        } else {
            $onetimePayment = $this->product->getPriceByCurrency($this->currency, $this->billing)->price * $this->quantity;
        }
        return $this->applyCoupon($onetimePayment, self::PRICE);
    }

    /**
     * Permet de récupérer les FAS original dans la base de données pour calculer les taxes
     * @param bool $withQuantity
     * @return float|int
     */
    public function dbsetup(bool $withQuantity = true)
    {
        $this->enableCoupon();
        if (!$withQuantity){
            $dbsetup = $this->product->getPriceByCurrency($this->currency, $this->billing)->dbsetup;
        } else {
            $dbsetup = $this->product->getPriceByCurrency($this->currency, $this->billing)->dbsetup * $this->quantity;
        }
        return $this->applyCoupon($dbsetup, self::SETUP_FEES);
    }
    /**
     * Permet de récupérer le prix original dans la base de données pour calculer les taxes
     * @param bool $withQuantity
     * @return float|int
     */
    public function dbprice(bool $withQuantity = true)
    {
        $this->enableCoupon();
        if (!$withQuantity){
            $dbprice = $this->product->getPriceByCurrency($this->currency, $this->billing)->dbprice;
        } else {
            $dbprice = $this->product->getPriceByCurrency($this->currency, $this->billing)->dbprice * $this->quantity;
        }
        return $this->applyCoupon($dbprice, self::PRICE);
    }
    /**
     * Renvoie le pourcentage de taxes de la commande
     * @return float
     */
    public function taxPercent()
    {
        return tax_percent();
    }
    /**
     * Renvoie le montant des taxes de la commande (subtotal * taxPercent)
     * @return float
     */
    public function tax()
    {
        return TaxesService::getTaxAmount($this->dbprice() + $this->dbsetup(), $this->taxPercent());
    }
    /**
     * The total with quantity
     * @return float|int
     */
    public function total()
    {
        return $this->subtotal() + $this->tax();
    }


    public function subtotal()
    {
        return $this->recurringPayment() + $this->setup();
    }

    public function basket()
    {
        return $this->belongsTo(Basket::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function setup(bool $withQuantity = true)
    {
        if (!$withQuantity){
            $setup =  $this->product->getPriceByCurrency($this->currency, $this->billing)->setup;
        } else {
            $setup = $this->product->getPriceByCurrency($this->currency, $this->billing)->setup * $this->quantity;
        }
        return $this->applyCoupon($setup, self::SETUP_FEES);
    }

    public static function findByProductOnSession(Product $product, bool $force = true):?BasketRow
    {
        $basket = Basket::getBasket();
        $row = self::where('basket_id', $basket->id)->where('product_id', $product->id)->first();
        if (!$force) {
            if ($row === null){
                $row = new BasketRow([
                    'product_id' => $product->id,
                    'basket_id' => Basket::getBasket()->id,
                    'currency' => currency(),
                ]);
            }
        } else {
            if ($row === null) {
                $row = new BasketRow([
                    'product_id' => $product->id,
                    'basket_id' => Basket::getBasket()->id,
                    'currency' => currency(),
                ]);
                $row->save();
            }
        }
        return $row;
    }

    public function primary()
    {
        if ($this->product->productType()->data($this->product) != null) {
            return $this->product->productType()->data($this->product)->primary(new ProductDataDTO($this->product, $this->data ?? [], $this->options ?? [], []));
        }
        return null;
    }

    public function name()
    {
        return $this->product->name;
    }

    public function canApplyCoupon(Coupon $coupon)
    {
        if ($coupon->products_required && !$coupon->is_global) {
            $products = $this->basket->rows->map(function ($row) {
                return $row->product_id;
            });
            if ($products->intersect($coupon->products->pluck('id'))->count() == 0) {
                return false;
            }
        }
        if ($coupon->products()->count() > 0) {
            if (!$coupon->products->contains($this->product->id)) {
                return false;
            }
        }
        return true;
    }

    public function getDiscountArray()
    {
        if ($this->basket->coupon == null) {
            return [];
        }
        /** @var Coupon $coupon */
        $coupon = $this->basket->coupon;
        $recurrings = app(RecurringService::class)->getRecurrings()->keys();
        return [
            'code' => $coupon->code,
            'type' => $coupon->type,
            'id' => $coupon->id,
            'applied_month' => $coupon->applied_month,
            'free_setup' => $coupon->free_setup,
            'pricing_price' => number_format($coupon->getPricingRecurring($this->billing, self::PRICE) ?? 0, 2),
            'pricing_setup' => number_format($coupon->getPricingRecurring($this->billing, self::SETUP_FEES) ?? 0, 2),
            'discount_unit_price' => number_format($this->recurringPaymentWithoutCoupon(false) - $this->recurringPayment(false), 2),
            'discount_unit_setup' => number_format($this->setupWithoutCoupon(false) - $this->setup(false), 2),
            'discount_price' => number_format($this->recurringPaymentWithoutCoupon() - $this->recurringPayment(), 2),
            'discount_setup' => number_format($this->setupWithoutCoupon() - $this->setup(), 2),
            'discounted_amount' => collect($recurrings)->mapWithKeys(function ($recurring) use ($coupon) {
                $recurringprice = $this->recurringPaymentWithoutCoupon(false, $recurring);
                $discountedValue = $coupon->getPricingRecurring($recurring, self::PRICE) ?? 0;
                if ($discountedValue == 0){
                    return [$recurring => 0];
                }
                if ($coupon->type == Coupon::TYPE_PERCENT) {
                    $discountedValue = number_format(($recurringprice * ($discountedValue / 100)), 2);
                }
                return [$recurring => $discountedValue];
            }),
            'values' => collect($recurrings)->mapWithKeys(function($recurring) use ($coupon) {
                return [$recurring => number_format($coupon->getPricingRecurring($recurring, self::PRICE) ?? 0)];
            })
        ];
    }
}
