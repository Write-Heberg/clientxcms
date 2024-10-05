<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Models\Provisioning;

use App\Abstracts\SupportRelateItemTrait;
use App\Core\NoneProductType;
use App\DTO\Store\ProductPriceDTO;
use App\Mail\Core\Service\NotifyExpirationEmail;
use App\Models\Account\Customer;
use App\Models\Store\Coupon;
use App\Models\Store\Product;
use App\Models\Traits\HasMetadata;
use App\Models\Traits\Loggable;
use App\Services\Store\RecurringService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *      schema="ProvisioningService",
 *     title="Shop pricing",
 *     description="Shop pricing model"
 * )
 */
class Service extends Model
{
    use HasFactory, Traits\ServerTypeTrait, HasMetadata, SupportRelateItemTrait, Loggable;
    const STATUS_ACTIVE = 'active';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_PENDING = 'pending';
    const STATUS_EXPIRED = 'expired';

    const FILTERS = [
        'all' => 'all',
        self::STATUS_ACTIVE => 'active',
        self::STATUS_SUSPENDED => 'suspended',
        self::STATUS_CANCELLED => 'cancelled',
        self::STATUS_PENDING => 'pending',
        self::STATUS_EXPIRED => 'expired',
    ];
    /**
     * @var string[] $fillable
     * @OA\Property(
     *     property="customer_id",
     *     type="integer",
     *     description="The ID of the associated customer",
     *     example=123
     *     ),
     * @OA\Property(
     *     property="name",
     *     type="string",
     *     description="The name of the service",
     *     example="Service name"
     *    ),
     * @OA\Property(
     *     property="type",
     *     type="string",
     *     description="The type of the service",
     *     example="proxmox"
     *   ),
     * @OA\Property(
     *     property="price",
     *     type="number",
     *     format="float",
     *     description="The price of the service",
     *     example=9.99
     *     ),
     * @OA\Property(
     *     property="billing",
     *     type="string",
     *     description="The billing of the service",
     *     example="monthly"
     *  ),
     * @OA\Property(
     *     property="initial_price",
     *     type="number",
     *     format="float",
     *     description="The initial price of the service",
     *     example=9.99
     *     ),
     * @OA\Property(
     *     property="server_id",
     *     type="integer",
     *     description="The ID of the associated server",
     *     example=1
     *     ),
     * @OA\Property(
     *     property="product_id",
     *     type="integer",
     *     description="The ID of the associated product (nullable)",
     *     example=1
     *     ),
     * @OA\Property(
     *     property="invoice_id",
     *     type="integer",
     *     description="The ID of the associated invoice for renewal (nullable)",
     *     example=123
     *     ),
     * @OA\Property(
     *     property="status",
     *     type="string",
     *     description="The status of the service",
     *     example="active"
     *    ),
     *  @OA\Property(
     *     property="expires_at",
     *     type="string",
     *     format="date-time",
     *     description="The expiration date of the service",
     *     example="2021-01-01 00:00:00"
     *   ),
     * @OA\Property(
     *     property="suspended_at",
     *     type="string",
     *     format="date-time",
     *     description="The suspension date of the service",
     *     example="2021-01-01 00:00:00"
     *  ),
     * @OA\Property(
     *     property="cancelled_at",
     *     type="string",
     *     format="date-time",
     *     description="The cancellation date of the service",
     *     example="2021-01-01 00:00:00"
     * ),
     * @OA\Property(
     *     property="cancelled_reason",
     *     type="string",
     *     description="The cancellation reason of the service",
     *     example="Service cancelled"
     * ),
     * @OA\Property(
     *     property="notes",
     *      type="string",
     *     description="The notes of the service",
     *     example="Service notes"
     * ),
     * @OA\Property(
     *     property="delivery_errors",
     *     type="STRING",
     *     description="The delivery errors of the service",
     *     example="Delivery errors"
     * ),
     * @OA\Property(
     *     property="delivery_attempts",
     *     type="integer",
     *     description="The delivery attempts of the service",
     *     example=1
     *     ),
     * @OA\Property(
     *     property="renewals",
     *     type="integer",
     *     description="The renewals of the service",
     *     example=1
     *     ),
     * @OA\Property(
     *     property="trial_ends_at",
     *     type="string",
     *     format="date-time",
     *     description="The trial end date of the service",
     *     example="2021-01-01 00:00:00"
     * ),
     * @OA\Property(
     *     property="max_renewals",
     *     type="integer",
     *     description="The maximum renewals of the service",
     *     example=1
     *     ),
     * @OA\Property(
     *     property="data",
     *     type="json",
     *     description="The data of the service",
     *     example={"key":"value"}
     *     ),
     * @OA\Property(
     *     property="currency",
     *     type="string",
     *     description="The currency of the service",
     *      example="USD"
     * ),
     * @OA\Property(
     *     property="suspend_reason",
     *     type="string",
     *     description="The suspension reason of the service",
     *     example="Service suspended"
     * ),
     */
    protected $fillable = [
        'customer_id',
        'name',
        'type',
        'price',
        'billing',
        'initial_price',
        'server_id',
        'product_id',
        'invoice_id',
        'status',
        'expires_at',
        'suspended_at',
        'cancelled_at',
        'cancelled_reason',
        'notes',
        'delivery_errors',
        'delivery_attempts',
        'renewals',
        'trial_ends_at',
        'max_renewals',
        'data',
        'currency',
        'suspend_reason',
        'is_cancelled',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'suspended_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'data' => 'array',
    ];
    protected $attributes = [
        'status' => self::STATUS_PENDING,
        'renewals' => 0,
        'delivery_attempts' => 0,
        'max_renewals' => NULL,
        'price' => 0,
        'initial_price' => 0,
    ];

    private ?ProductPriceDTO $mainPrice = null;
    public ?Carbon $last_expires_at = null;

    public static function getStatuses()
    {
        return [
            self::STATUS_ACTIVE => __('global.states.active'),
            self::STATUS_SUSPENDED => __('global.states.suspended'),
            self::STATUS_CANCELLED => __('global.states.cancelled'),
            self::STATUS_PENDING => __('global.states.pending'),
            self::STATUS_EXPIRED => __('global.states.expired'),
        ];
    }

    public static function countCustomers(bool $active = false)
    {
        if ($active)
            return self::where('status', self::STATUS_ACTIVE)->select('customer_id')->get()->unique('customer_id')->count();
        return self::select('customer_id')->get()->unique('customer_id')->count();
    }

    public static function getShouldCreateInvoice()
    {
        return self::where('status', self::STATUS_ACTIVE)
            ->whereNull('invoice_id')
            ->whereNull('cancelled_at')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now()->addDays(setting('core.services.days_before_creation_renewal_invoice')))
            ->get();
    }

    public static function getShouldExpire()
    {
        return self::where('status', self::STATUS_SUSPENDED)
            ->whereNull('cancelled_at')
            ->whereNotNull('expires_at')
            ->whereRaw('NOW() >= DATE_ADD(expires_at, INTERVAL ? DAY)', [setting('core_services_days_before_expiration')])
            ->get();
    }

    public static function getShouldSuspend()
    {
        return self::where('status', self::STATUS_ACTIVE)
            ->whereNull('cancelled_at')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();
    }

    public static function getShouldCancel()
    {
        return self::whereNotNull('cancelled_at')
            ->whereNotNull('cancelled_reason')
            ->where('is_cancelled', false)
            ->whereRaw('NOW() >= cancelled_at')
            ->get();
    }

    public static function getShouldNotifyExpiration(array $days)
    {
        return self::where('status', self::STATUS_ACTIVE)
            ->whereNull('cancelled_at')
            ->whereNotNull('expires_at')
            ->where(function ($query) use ($days) {
                foreach ($days as $day) {
                    $query->orWhereRaw('DATEDIFF(expires_at, NOW()) = ?', [$day]);
                }
            })->get();
    }

    public function pricingAvailable(bool $all = false)
    {
        if ($this->product_id == null) {
            if ($all)
                return [];
            return collect(app(RecurringService::class)->getRecurrings())->mapWithKeys(function($k, $v) {
                return [$v => $k['translate']];
            })->toArray();
        }
        $recurrings = app(RecurringService::class)->getRecurrings();
        $pricing = $this->product->pricingAvailable($this->currency);
        if ($all)
            return $pricing;
        return collect($pricing)->mapWithKeys(function($price) use ($recurrings){
            return [$price->recurring => $recurrings[$price->recurring]['label'] . ' - ' . $price->getSymbol() . $price->dbprice];
        })->toArray();
    }

    public function invoice()
    {
        return $this->belongsTo(\App\Models\Core\Invoice::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function server()
    {
        return $this->belongsTo(Server::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function renewals()
    {
        return $this->hasMany(ServiceRenewals::class);
    }

    public function productType()
    {
        return app('extension')->getProductTypes()->get($this->type, new NoneProductType());
    }

    public function recurring()
    {
        return app(RecurringService::class)->get($this->billing);
    }

    public function canRenew()
    {
        if ($this->expires_at == null)
            return false;
        if ($this->billing == 'free' || $this->billing == 'onetime'){
            return false;
        }
        $keys = ['week' => [now()->startOfWeek(), now()->endOfWeek()], 'month' => [now()->startOfMonth(), now()->endOfMonth()]];
        foreach ($keys as $key => $dates) {
            if ($this->getMetadata("max_renewals_in_current_{$key}")){
                $maxRenewals = $this->getMetadata("max_renewals_in_current_{$key}");
                $renewals = $this->renewals()->whereBetween('created_at', $dates)->count();
                if ($renewals >= $maxRenewals){
                    return false;
                }
            }
        }
        if ($this->max_renewals != NULL){
            return $this->renewals < $this->max_renewals;
        }
        if (in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_SUSPENDED])){
            return true;
        }
        return false;
    }

    public function isFree()
    {
        return $this->price == 0;
    }

    public function canManage()
    {
        return in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_SUSPENDED, self::STATUS_CANCELLED]);
    }

    public function isActivated()
    {
        return $this->status == self::STATUS_ACTIVE;
    }

    public function isSuspended()
    {
        return $this->status == self::STATUS_SUSPENDED;
    }

    public function isCancelled()
    {
        return $this->status == self::STATUS_CANCELLED;
    }

    public function isPending()
    {
        return $this->status == self::STATUS_PENDING;
    }

    public function isExpired()
    {
        return $this->status == self::STATUS_EXPIRED;
    }

    public function isOneTime()
    {
        return $this->billing == 'onetime';
    }

    public function getInvoiceName()
    {
        if ($this->product_id == null)
            return $this->name;
        if ($this->billing == 'onetime')
            return $this->name;
        $current = $this->expires_at->format('d/m/y');
        $expiresAt = app(RecurringService::class)->addFrom($this->expires_at, $this->billing);
        return "{$this->name} ({$current} - {$expiresAt->format('d/m/y')})";
    }

    public function hasDiscount(string $recurring)
    {
        if (!$this->hasMetadata('discount')){
            return false;
        }

        $discount = json_decode($this->getMetadata('discount'));
        if ($discount->applied_month == Coupon::APPLIED_MONTH_FIRST) {
            return false;
        } else if ($discount->applied_month != Coupon::APPLIED_MONTH_UNLIMITED){
            if ($this->renewals >= $discount->applied_month) {
                return false;
            }
            $pricings = $discount->discounted_amount;
            if (!property_exists($pricings, $recurring)){
                return false;
            }
            return $pricings->$recurring != 0;
        }
        $pricings = $discount->discounted_amount;
        if (!property_exists($pricings, $recurring)){
            return false;
        }
        return $pricings->$recurring != 0;
    }

    public function getDiscountOnRecurring(string $recurring)
    {
        if (!$this->hasMetadata('discount')){
            return 0;
        }
        $discount = json_decode($this->getMetadata('discount'));
        if ($discount->applied_month == Coupon::APPLIED_MONTH_FIRST) {
            return 0;
        } else if ($discount->applied_month != Coupon::APPLIED_MONTH_UNLIMITED){
            if ($this->renewals >= $discount->applied_month) {
                return 0;
            }
            $pricings = $discount->discounted_amount;
            if (!property_exists($pricings, $recurring)){
                return 0;
            }
            return$pricings->$recurring;
        } else {
            $pricings = $discount->discounted_amount;
            if (!property_exists($pricings, $recurring)){
                return 0;
            }
            return $pricings->$recurring;
        }
    }

    public function generateDiscountedPrice(string $billing)
    {
        if (!$this->hasMetadata('discount')){
            return $this->price;
        }
        $defaultPrice = $this->getMetadata('default_price') ?? $this->price;
        return $defaultPrice - $this->getDiscountOnRecurring($billing);
    }

    public function getDiscountRenewal()
    {
        if (!$this->hasMetadata('discount')){
            return NULL;
        }
        if ($this->getDiscountOnRecurring($this->billing) == 0){
            return NULL;
        }
        $discount = json_decode($this->getMetadata('discount'));
        $defaultPrice = $this->getMetadata('default_price') ?? $this->price;
        $discount->discount_price = $defaultPrice - $this->getDiscountOnRecurring($this->billing);
        $discount->discount_unit_price = $defaultPrice - $discount->discount_price;
        $discount->discount_setup = 0;
        $discount->discount_unit_setup = 0;
        $discount->pricing_price = $discount->values->{$this->billing} ?? 0;
        return (array)$discount;
    }

    public function renewPrice(string $currency, string $billing, bool $default = false)
    {
        $defaultPrice = $this->getMetadata('default_price') ?? $this->price;
        if ($this->product_id <= null)
            return $this->price;
        if ($default){
            return $defaultPrice;
        }
        $productPrice = $this->product->getPriceByCurrency($currency, $billing);
        if ($this->mainPrice == null){
            $mainPrice = $this->product->getPriceByCurrency($currency, $this->billing);
            $this->mainPrice = $mainPrice;
        } else {
            $mainPrice = $this->mainPrice;
        }
        if ($this->price != $mainPrice->price){
            return $this->price;
        } else {
            return $productPrice->price;
        }
    }

    public function relatedName(): string
    {
        return __('global.service') . ' #' . $this->id . ' - ' . $this->name . ' - ' . $this->status . ' - '. ($this->expires_at ? $this->expires_at->format('d/m/y') : 'None');
    }

    /**
     * @return bool
     */
    public function notifyExpiration():bool
    {
        $remaining = $this->expires_at->diffInDays(now());
        if ($remaining <= 0){
            return false;
        }
        if ($remaining == 7 && $this->billing == 'weekly'){
            return false;
        }
        if ($this->getMetadata('disable_notify_expiration') == true){
            return false;
        }
        $this->customer->notify(new NotifyExpirationEmail($this, $remaining));
        return true;
    }

}
