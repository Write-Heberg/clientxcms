<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Models\Store;

use App\DTO\Store\ProductPriceDTO;
use App\Models\Traits\HasMetadata;
use App\Models\Traits\Loggable;
use App\Models\Traits\ModelStatutTrait;
use App\Services\Store\CurrencyService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *      schema="ShopGroup",
 *     title="Shop group",
 *     description="Shop group model"
 * )
 */
class Group extends Model
{
    use HasFactory;
    use ModelStatutTrait;
    use HasMetadata;
    use Loggable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     *
     * @OA\Property(
     *     property="id",
     *     type="integer",
     *     description="The id of the item",
     *     example="10"
     * ),
     * @OA\Property(
     *     property="name",
     *     type="string",
     *     description="The name of the item",
     *     example="Sample Item"
     * )
     * @OA\Property(
     *     property="slug",
     *     type="string",
     *     description="The URL-friendly slug for the item",
     *     example="sample-item"
     * )
     * @OA\Property(
     *     property="status",
     *     type="string",
     *     description="The status of the item (e.g., active, inactive)",
     *     example="active"
     * )
     * @OA\Property(
     *     property="description",
     *     type="string",
     *     description="A description or details about the item",
     *     example="This is a sample item description."
     * )
     *
     * @OA\Property(
     *     property="sort_order",
     *     type="integer",
     *     description="The order in which the item should be sorted",
     *     example=1
     * )
     * @OA\Property(
     *     property="group_id",
     *     type="integer",
     *     nullable=true,
     *     description="The id of the group to which the item belongs",
     *     example=1
     * )
     * @OA\Property(
     *     property="pinned",
     *     type="boolean",
     *     description="Whether the item is pinned or not",
     *     example=true
     * )
     * @OA\Property(
     *     property="image",
     *     type="string",
     *     description="The URL or path to the item's image",
     *     example="https://example.com/images/sample.jpg"
     * )
     */
    protected $fillable = [
        'name',
        'slug',
        'status',
        'description',
        'sort_order',
        'pinned',
        'image',
        'parent_id',
    ];

    protected $casts = [
        'pinned' => 'boolean',
    ];
    protected $attributes = [
        'parent_id' => null,
        'status' => 'active',
        'sort_order' => 0,
        'pinned' => false,
    ];

    public static function parents()
    {
        return self::where('parent_id', null);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function groups(){
        return $this->hasMany(Group::class, 'parent_id');
    }
    public function group()
    {
        if ($this->parent_id == null){
            return null;
        }
        return Group::find($this->parent_id);
    }

    /**
     * Get first price of current group
     * @param string|null $recurring
     * @param string|null $currency
     * @return ProductPriceDTO
     */
    public function startPrice(?string $recurring = null, ?string $currency = null):ProductPriceDTO
    {
        if ($recurring == null){
            $recurring = "monthly";
        }

        if ($currency == null){
            $currency = app(CurrencyService::class)->retrieveCurrency();
        }
        $products = $this->products->where('status', 'active')->all();
        foreach ($this->groups as $group){
            $products = array_merge($products, $group->products->where('status', 'active')->all());
        }
        $prices = [];
        /** @var Product $product */
        foreach ($products as $product){
            if ($product->isPersonalized())
                continue;
            $price = $product->getPriceByCurrency($currency, $recurring);
            if ($price->dbprice == 0){
                $price = $product->getFirstPrice();
            }
            $prices[] = $price->dbprice;
            $currency = $price->currency;
        }
        sort($prices);
        return new ProductPriceDTO($prices[0] ?? 0,0, $currency, $recurring);
    }

    public function route(bool $absolute = true)
    {
        if ($this->parent_id){
            return route('front.store.subgroup', [$this->group()->slug, $this->slug], $absolute);
        }
        return route('front.store.group', $this->slug, $absolute);
    }

    public function isSubgroup()
    {
        return $this->parent_id !== null;
    }

    public function isGroup()
    {
        return $this->parent_id === null;
    }

    public function useImageAsBackground(): bool
    {
        return $this->getMetadata('use_image_as_background') === 'true';
    }
}
