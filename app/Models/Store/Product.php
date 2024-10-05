<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Models\Store;

use App\Contracts\Store\ProductTypeInterface;
use App\Core\NoneProductType;
use App\DTO\Store\ProductPriceDTO;
use App\Models\Provisioning\Service;
use App\Models\Traits\HasMetadata;
use App\Models\Traits\Loggable;
use App\Models\Traits\ModelStatutTrait;
use App\Services\Store\CurrencyService;
use App\Services\Store\PricingService;
use App\Services\Store\ProductTypeService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *      schema="ShopProduct",
 *     title="Shop product",
 *     description="Shop product model"
 * )
 */
class Product extends Model
{
    use HasFactory;
    use HasMetadata;
    use ModelStatutTrait;
    use Loggable;

    const UNLIMITED_STOCK = -1;

    /**
     * @var string[] $fillable
     *
     * @OA\Property(
     *     property="id",
     *     type="integer",
     *     description="The id of the item",
     *     example="10"
     * ),
     *  * @OA\Property(
     *     property="name",
     *     type="string",
     *     description="The name of the item",
     *     example="Sample Item"
     * )
     * @OA\Property(
     *     property="status",
     *     type="string",
     *     description="The status of the item (e.g., Active, Hidden, Unreferenced)",
     *     example="active"
     * )
     * @OA\Property(
     *     property="description",
     *     type="string",
     *     description="A description or details about the item",
     *     example="This is a sample item description."
     * )
     * @OA\Property(
     *     property="sort_order",
     *     type="integer",
     *     description="The order in which the item should be sorted",
     *     example=1
     * )
     * @OA\Property(
     *     property="group_id",
     *     type="integer",
     *     description="The ID of the group to which the item belongs",
     *     example=123
     * )
     * @OA\Property(
     *     property="stock",
     *     type="integer",
     *     description="The stock quantity of the item",
     *     example=50
     * )
     * @OA\Property(
     *     property="type",
     *     type="string",
     *     description="The type of the product",
     *     example="pterodactyl"
     * )
     * @OA\Property(
     *     property="pinned",
     *     type="boolean",
     *     description="Whether the item is pinned or not",
     *     example=true
     * )
     */
    protected $fillable = [
        'name',
        'status',
        'description',
        'sort_order',
        'group_id',
        'stock',
        'type',
        'pinned',
    ];

    protected $casts = [
        'pinned' => 'boolean',
    ];

    protected $attributes = [
        'status' => 'active',
        'stock' => 10,
        'sort_order' => 0,
    ];

    public static function getAllProducts(bool $inAdmin = false)
    {
        return self::getAvailable($inAdmin)->pluck('name', 'id')->mapWithKeys(function ($name, $id) {
            return [$id => $name];
        });
    }

    public static function addStock(?int $id= null)
    {
        if ($id == null){
            return;
        }
        $product = self::find($id);
        if ($product == null){
            return;
        }

        if ($product->getMetadata('auto_stock') != null){
            $product->stock += 1;
            $product->save();
        }
        if ($product->stock == self::UNLIMITED_STOCK){
            return;
        }
    }

    public function isOutOfStock(): bool
    {
        if ($this->stock == self::UNLIMITED_STOCK){
            return false;
        }
        return $this->stock == 0;
    }

    public static function removeStock(?int $id = null)
    {
        if ($id == null){
            return;
        }
        $product = self::find($id);
        if ($product == null){
            return;
        }
        if ($product->getMetadata('auto_stock') != null || $product->stock > 0) {
            $product->stock -= 1;
            $product->save();
        }
    }
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function pricing()
    {
        return $this->hasMany(Pricing::class, 'related_id')->where('related_type', 'product');
    }

    public function getFirstPrice(): ProductPriceDTO
    {
        /** @var Pricing|null $pricing */
        $pricing = $this->pricing()->first();
        if ($pricing == null){
            return new ProductPriceDTO(0, 0, currency(), 'monthly');
        }
        $first = $pricing->getFirstRecurringType();
        if ($first === null)
            return new ProductPriceDTO(0, 0, currency(), 'monthly');
        return new ProductPriceDTO($pricing->$first, $pricing->{"setup_$first"}, $pricing->currency, 'monthly');
    }

    public function pricingAvailable(?string $currency = null)
    {
        $recurrings = Pricing::getRecurringTypes();
        $available = [];

        if ($currency === null) {
            $pricing = PricingService::forProduct($this->id);
            if ($pricing->isEmpty()){
                return [];
            }
        } else {
            $pricing = [PricingService::forProductCurrency($this->id, $currency)];
        }
        if (current($pricing) == null){
            return $this->pricingAvailable();
        }
        foreach ($pricing as $price) {
            foreach ($recurrings as $recurring) {
                if ($price[$recurring] !== null) {
                    if (!is_float($price[$recurring])){
                        continue;
                    }
                    $available[] = new ProductPriceDTO($price[$recurring], $price["setup_".$recurring], $price['currency'], $recurring);
                }
            }
        }
        return $available;
    }

    public function hasPricesForCurrency(?string $currency = null): bool
    {
        if ($currency == null){
            $currency = app(CurrencyService::class)->retrieveCurrency();
        }
        return $this->getPriceByCurrency($currency) !== null;
    }

    public function getPriceByCurrency(string $currency, ?string $recurring = null):ProductPriceDTO
    {
        $price = 0;
        $pricing = PricingService::forProductCurrency($this->id, $currency);
        $setup = 0;
        if ($pricing == null){
            $pricing = PricingService::forProduct($this->id)->first();
            if ($pricing != null){
                $pricing = new Pricing($pricing);
                $currency = $pricing->currency;
                if ($recurring == null){
                    $recurring = $pricing->getFirstRecurringType() ?? 'monthly';
                }
                $price = $pricing->getAttribute($recurring) ?? 0;
                $setup = $pricing->getAttribute("setup_".$recurring) ?? 0;
            } else {
                $recurring = 'monthly';
            }
        } else {
            $pricing = new Pricing($pricing);
            if ($recurring == null){
                $recurring = $pricing->getFirstRecurringType() ?? 'monthly';
            }
            $price = $pricing->getAttribute($recurring) ?? 0;
            $setup = $pricing->getAttribute("setup_".$recurring) ?? 0;
        }
        return new ProductPriceDTO($price, $setup, $currency, $recurring);
    }

    public function basket_url()
    {
        if ($this->hasMetadata('basket_url')){
            return $this->getMetadata('basket_url');
        }
        if ($this->hasMetadata('is_personalized_product')){
            if ($this->hasMetadata('personalized_product_url')){
                return $this->getMetadata('personalized_product_url');
            }
            return route('front.support.create');
        }
        return route('front.store.basket.add', $this->id);
    }

    public function data_url()
    {
        return route('front.store.basket.config', $this->id);
    }

    public function basket_title()
    {
        if ($this->hasMetadata('basket_title')){
            return $this->getMetadata('basket_title');
        }
        if ($this->hasMetadata('is_personalized_product')){
            return trans('store.basket.contactus');
        }
        return trans('store.basket.addtocart');
    }

    public function isPersonalized(): bool
    {
        return $this->hasMetadata('is_personalized_product');
    }

    public function productType(): ProductTypeInterface
    {
        return app('extension')->getProductTypes()->get($this->type, new NoneProductType());
    }

    public function canAddToBasket(): bool
    {
        $option = $this->getMetadata('disabled_many_services');
        if ($option != null){
            if ($option == 'active') {
                $service = Service::where('product_id', $this->id)->where('customer_id',auth('web')->id())->where('status', Service::STATUS_ACTIVE)->first();
                if ($service != null){
                    return false;
                }
            }
            if ($option == 'all') {
                $service = Service::where('product_id', $this->id)->where('customer_id',auth('web')->id())->first();
                if ($service != null){
                    return false;
                }
            }
        }
        return true;
    }
}
