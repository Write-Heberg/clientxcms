<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Models\Store\Basket;

use App\Models\Account\Customer;
use App\Models\Store\Coupon;
use App\Models\Traits\HasMetadata;
use App\Services\Store\TaxesService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use Str;

class Basket extends Model
{
    use HasFactory;
    use HasMetadata;
    use BasketCouponTrait;

    protected $fillable = [
        'user_id',
        'uuid',
        'coupon_id',
        'completed_at',
        'ip_address',
    ];

    public function rows()
    {
        return $this->hasMany(BasketRow::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items()
    {
        return $this->rows();
    }

    /**
     * Renvoie le pourcentage de taxes de la commande
     * @return float
     */
    public function taxPercent()
    {
        return tax_percent();
    }

    public function dbprice()
    {
        return $this->rows->reduce(function ($total, $row) {
            return $total + $row->dbprice(true);
        }, 0);
    }

    public function dbsetup()
    {
        return $this->rows->reduce(function ($total, $row) {
            return $total + $row->dbsetup(true);
        }, 0);
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
     * Renvoie-le sous total de la commande (produits + options) sans les taxes
     * @return float
     */
    public function subtotal():float
    {
        return $this->setup() + $this->recurringPayment() + $this->onetimePayment();
    }

    public function onetimePayment()
    {
        return $this->rows->reduce(function ($total, $row) {
            return $total + $row->onetimePayment(true);
        }, 0);
    }

    /**
     * Renvoie les frais d'installation de la commande
     * @return float
     */
    public function setup()
    {
        return $this->rows->reduce(function ($total, $row) {
            return $total + $row->setup(true);
        }, 0);
    }

    /**
     * Renvoie la quantité des items de la commande
     * @return int
     */
    public function quantity()
    {
        return (int)$this->rows()->sum('quantity');
    }

    /**
     * Renvoie le montant total de la commande (subtotal + taxes)
     * @return float
     */
    public function total():float
    {
        return $this->subtotal() + $this->tax();
    }

    /**
     * Renvoie le montant total des paiements récurrents de la commande
     * @return float
     */
    public function recurringPayment():float
    {
        return $this->rows->reduce(function ($total, $row) {
            return $total + $row->recurringPayment(true);
        }, 0);
    }

    /**
     * Renvoie la devise de la commande
     * @return string
     */
    public function currency()
    {
        return $this->rows->first()->currency ?? currency();
    }

    /**
     * Permet de vérifier que chaque item de la commande a la même devise
     * @return bool
     */
    public function checkCurrency():bool
    {
        $currency = null;
        $first = $this->rows->first();
        if ($first != null){
            $currency = $first->currency;
        }
        $filter = $this->rows->filter(function ($row) use ($currency){
            return $currency != null && $currency != $row->currency;
        });
        $count = $filter->count();
        if ($count > 0){
            $filter->each(function ($row){
                $row->delete();
            });
            return false;
        }
        return true;
    }

    /**
     * Permet de vérifier que chaque item de la commande est valide
     * @return bool
     */
    public function checkValid():bool
    {
        $filter = $this->rows->filter(function ($row){
            if ($row->product->stock == -1){
                return false;
            }
            return $row->product->isNotValid(true) || $row->quantity > $row->product->stock || $row->quantity > 100;
        });
        $count = $filter->count();
        if ($count > 0){
            $filter->each(function ($row){
                $row->delete();
            });
            return false;
        }
        return true;
    }

    public static function getBasket(bool $force = true)
    {
        $uuid = self::getUUID();
        if ($force) {
            return self::firstOrCreate([
                'user_id' => auth('web')->id(),
                'uuid' => $uuid,
                'completed_at' => null,
            ]);
        }
        return self::where('user_id', auth('web')->id())->where('uuid', $uuid)->whereNull('completed_at')->first();
    }

    public function customer()
    {
        if ($this->user_id != null){
            return $this->belongsTo(Customer::class, 'user_id');
        }
        return null;
    }
    /**
     * Permet de fusionner le panier de l'utilisateur avec le panier de l'invité
     * @param Customer $user
     * @return void
     */
    public function mergeBasket(Customer $user)
    {
        $sessionUUID = request()->session()->get('basket_uuid');
        if ($sessionUUID == null){
            return;
        }
        $basket = self::where('uuid', $sessionUUID)->first();

        if ($basket) {
            /** @var BasketRow $row */
            foreach ($basket->rows as $row){
                $row->update(['basket_id' => $this->id]);
            }
            $this->refresh();
            $basket->refresh();
        }
        $this->update(['user_id' => $user->id, 'coupon_id' => $basket ? $basket->coupon_id : null]);
        foreach ($this->rows as $row) {
            if (!$row->product->canAddToBasket()){
                $row->delete();
                Session::flash('error', __('store.basket.already_ordered', ['product' => $row->product->name]));
            }
        }
        if ($this->coupon != null){
            if (!$this->coupon->isValid($this, true)){
                $this->update(['coupon_id' => null]);
            }
        }
    }

    public function clear(bool $completed = false)
    {
        $this->rows->each(function ($row){
            $row->delete();
        });
        if ($completed){
            $this->update(['completed_at' => now()]);
            if (auth('web')->id() == null){
                request()->session()->forget('basket_uuid');
            } else {
                auth('web')->user()->attachMetadata('basket_uuid',null);
            }
        }
    }

    public function applyCoupon(string $couponName)
    {
        $coupon = Coupon::where('code', $couponName)->first();
        if ($coupon == null){
            Session::flash('error', __('coupon.not_found'));
            return false;
        }
        if (!$coupon->isValid($this, true)){
            return false;
        }
        $this->update(['coupon_id' => $coupon->id]);
        return true;
    }

    public static function getUUID()
    {
        if (auth('web')->id() != null) {
            $uuid = auth('web')->user()->getMetadata('basket_uuid');
            if ($uuid == null){
                $uuid = Str::uuid();
                auth('web')->user()->attachMetadata('basket_uuid', $uuid);
            }
        } else {
            $uuid = request()->session()->get('basket_uuid');
            if ($uuid == null){
                $uuid = Str::uuid();
                request()->session()->put('basket_uuid', $uuid);
            }
        }
        return $uuid;
    }

    public function discount(string $type)
    {
        if ($type == BasketRow::PRICE){
            $initial = $this->rows->reduce(function ($total, BasketRow $row){
                return $total + $row->recurringPaymentWithoutCoupon();
            }, 0);
            $discount = $this->rows->reduce(function ($total, BasketRow $row){
                return $total + $row->recurringPayment();
            }, 0);
            return $initial - $discount;
        }
        $initial = $this->rows->reduce(function ($total, BasketRow $row){
            return $total + $row->setupWithoutCoupon();
        }, 0);
        $discount = $this->rows->reduce(function ($total, BasketRow $row){
            return $total + $row->setup();
        }, 0);
        return $initial - $discount;
    }
}
