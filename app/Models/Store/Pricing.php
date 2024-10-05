<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Models\Store;

use App\Services\Store\RecurringService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * @OA\Schema(
 *      schema="ShopPricing",
 *     title="Shop pricing",
 *     description="Shop pricing model"
 * )
 */
class Pricing extends Model
{
    use HasFactory;

    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     * @OA\Property(
     *     property="related_id",
     *     type="integer",
     *     description="The ID of the associated item",
     *     example=123
     * ),
     *
     * @OA\Property(
     *     property="related_type",
     *     type="string",
     *     description="The type of the associated item",
     *     example=product
     * ),
     * @OA\Property(
     *     property="currency",
     *     type="string",
     *     description="The currency for pricing",
     *     example="USD"
     * ),
     * @OA\Property(
     *     property="onetime",
     *     type="number",
     *     format="float",
     *     nullable=true,
     *     description="One-time payment amount",
     *     example=99.99
     * ),
     * @OA\Property(
     *     property="monthly",
     *     type="number",
     *     format="float",
     *     nullable=true,
     *     description="Monthly payment amount",
     *     example=9.99
     * ),
     * @OA\Property(
     *     property="quarterly",
     *     type="number",
     *     format="float",
     *     nullable=true,
     *     description="Quarterly payment amount",
     *     example=24.99
     * ),
     * @OA\Property(
     *     property="semiannually",
     *     type="number",
     *     format="float",
     *     nullable=true,
     *     description="Semi-annual payment amount",
     *     example=49.99
     * ),
     * @OA\Property(
     *     property="annually",
     *     type="number",
     *     format="float",
     *     nullable=true,
     *     description="Annual payment amount",
     *     example=99.99
     * ),
     * @OA\Property(
     *     property="biennially",
     *     type="number",
     *     format="float",
     *     nullable=true,
     *     description="Biennial payment amount",
     *     example=199.99
     * ),
     * @OA\Property(
     *     property="triennially",
     *     type="number",
     *     format="float",
     *     nullable=true,
     *     description="Triennial payment amount",
     *     example=299.99
     * ),
     * @OA\Property(
     *     property="setup_onetime",
     *     type="number",
     *     format="float",
     *     nullable=true,
     *     description="One-time setup fee amount",
     *     example=19.99
     * ),
     * @OA\Property(
     *     property="setup_monthly",
     *     type="number",
     *     format="float",
     *     nullable=true,
     *     description="Monthly setup fee amount",
     *     example=4.99
     * ),
     * @OA\Property(
     *     property="setup_quarterly",
     *     type="number",
     *     format="float",
     *     nullable=true,
     *     description="Quarterly setup fee amount",
     *     example=9.99
     * ),
     * @OA\Property(
     *     property="setup_semiannually",
     *     type="number",
     *     format="float",
     *     nullable=true,
     *     description="Semi-annual setup fee amount",
     *     example=14.99
     * ),
     * @OA\Property(
     *     property="setup_annually",
     *     type="number",
     *     format="float",
     *     nullable=true,
     *     description="Annual setup fee amount",
     *     example=29.99
     * ),
     * @OA\Property(
     *     property="setup_biennially",
     *     type="number",
     *     format="float",
     *     nullable=true,
     *     description="Biennial setup fee amount",
     *     example=49.99
     * ),
     * @OA\Property(
     *     property="setup_triennially",
     *     type="number",
     *     format="float",
     *     nullable=true,
     *     description="Triennial setup fee amount",
     *     example=69.99
     * )
     */

    protected $fillable = [
        'related_id',
        'related_type',
        'currency',
        'onetime',
        'monthly',
        'quarterly',
        'semiannually',
        'annually',
        'biennially',
        'triennially',
        'weekly',
        'setup_onetime',
        'setup_monthly',
        'setup_quarterly',
        'setup_semiannually',
        'setup_annually',
        'setup_biennially',
        'setup_triennially',
        'setup_weekly',
    ];

    const ALLOWED_TYPES = ['product', 'service'];
    public function product()
    {
        return $this->belongsTo(Product::class, 'related_id')->where('related_type', 'product');
    }

    public static function getRecurringTypes():array
    {
        return app(RecurringService::class)->getRecurringTypes();
    }

    public function getFirstRecurringType():?string
    {
        return collect($this->getAttributes())->filter(function ($value, $key) {
            // check product id pour les anciennes versions
            if ($key === 'id' || $key === 'related_id' || $key === 'currency' || $key == 'related_type' || $key == 'product_id')
                return false;
            return $value !== null && !str_contains($key, 'setup');
        })->keys()->first();
    }

    public static function createFromArray(array $data, int $id, string $type = 'product'): void
    {
        $tmp = [];
        $tmp['related_id'] = $id;
        $tmp['related_type'] = $type;
        $tmp['currency'] = currency();
        foreach ($data['pricing'] as $recurring => $price) {
            $tmp[$recurring] = $price['price'];
            $tmp['setup_'.$recurring] = $price['setup'];
        }
        $pricing = new self();
        $pricing->fill($tmp);
        $pricing->save();
    }

    public function updateFromArray(array $data, string $type = 'product'): void
    {
        $tmp = [];
        $tmp['currency'] = currency();
        foreach ($data['pricing'] as $recurring => $price) {
            $tmp[$recurring] = $price['price'];
            $tmp['setup_'.$recurring] = $price['setup'];
        }
        $this->fill($tmp);
        $this->save();
    }

}
